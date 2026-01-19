<?php declare(strict_types = 1);

namespace Cbax\ModulLexicon\Components;

use Doctrine\DBAL\Connection;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use Cbax\ModulLexicon\Bootstrap\Database;

use Cbax\ModulLexicon\Components\Client\GuzzleClient;

class LexiconMigration
{
    private array|null $config;
    private string $baseUrl;
    private string $urlQuery;

    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly Connection $connection,
        private readonly EntityRepository $lexiconProductRepository,
        private readonly EntityRepository $lexiconSalesChannelRepository
    ) {}
    public function importData($start, $limit, $lexiconHelper, $context) : array
    {
        $result = ['success' => false, 'msg' => 'cbax-lexicon.config.notification.emptySettings',
            'successData' => ['countExistingLexiconEntries' => 0]
        ];

        $this->setPhpLimits();

        $this->config = $this->systemConfigService->get(Database::CONFIG_PATH);

        // Einstellungen prüfen
        if (empty($this->config['urlImport']) || empty($this->config['cronKeyImport'])) {
            return $result;
        }

        if (stripos($this->config['urlImport'], 'http://') === false && stripos($this->config['urlImport'], 'https://') === false) {
            $this->config['urlImport'] = 'http://' . $this->config['urlImport'];
        }

        $this->baseUrl = $this->config['urlImport'] . '/backend/LexiconMigration/';
        $this->urlQuery = '?key=' . $this->config['cronKeyImport'];

        $route = 'getEntries';
        $type = 'lexiconEntries';

        $lexiconEntriesComplete = $this->getSw5Data($result, $route, $start, $limit, $type);

        if (!empty($lexiconEntriesComplete['error'])) {
            return $lexiconEntriesComplete;
        }

        $lexiconEntries = $lexiconEntriesComplete['data'];

        if ($lexiconEntriesComplete['total'] === 0) {
            // keine Daten gefunden
            $result['msg'] = 'cbax-lexicon.config.notification.errorNoSw5Entries';
            return $result;
        }

        $result['successData']['totalLexiconEntries'] = (int) $lexiconEntriesComplete['total'];

        $lexiconKeywords = [];
        $lexiconIds = [];
        foreach ($lexiconEntries as $index => $lexiconEntry) {
            $lexiconKeywords[$index] = $lexiconEntry['keyword'];
            $lexiconIds[$index] = $lexiconEntry['id'];
        }

        // ggf. vorhandene Einträge suchen - entweder mit gleichem unique Kriterium oder bereits importierte (importId != null)
        $sql = '
            SELECT DISTINCT SQL_CALC_FOUND_ROWS le.`id`, `keyword`, `import_id`, `product_stream_id` FROM `cbax_lexicon_entry` le
            INNER JOIN `cbax_lexicon_entry_translation` lt ON le.id = lt.cbax_lexicon_entry_id
            WHERE keyword IN ("' . implode('","', $lexiconKeywords) . '") or import_id is not null
        ';

        $lexiconEntriesFromSw6 = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $sqlCount= "SELECT FOUND_ROWS() as count";
        $countResult = $this->connection->executeQuery($sqlCount)->fetchAssociative();

        $result['successData']['countExistingLexiconEntries'] = $countResult['count'];

        $streamConnection = [];
        $streamNames = [];
        foreach ($lexiconEntriesComplete['streams'] as $stream) {
            $streamConnection[$stream['lexiconId']] = $stream['streamId'];
            $streamNames[$stream['streamId']] = $stream['streamName'];
        }

        $result['successData']['totalSw5StreamConnections'] = count($streamConnection);
        $result['successData']['totalSw5StreamsConnected'] = count($streamNames);


        $sql = 'SELECT `product_stream_id`, `name` FROM `product_stream_translation` WHERE name IN ("' . implode('","', $streamNames) . '") GROUP BY name HAVING count(name) = 1';

        $resultSw6ProductStreams = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $mappedStreams = [];
        $notFoundStreams = $streamNames;
        foreach ($resultSw6ProductStreams as $sw6Streams) {
            $sw5StreamId = array_search($sw6Streams['name'], $streamNames);
            $mappedStreams[$sw5StreamId] = Uuid::fromBytesToHex($sw6Streams['product_stream_id']);

            unset($notFoundStreams[$sw5StreamId]);
        }

        if (count($notFoundStreams) > 0) {
            $lexiconHelper->doShopwareLog(array_values($notFoundStreams), 'Product Streams not found / Produkt Streams nicht gefunden', $context, 'Info', 'Lexikon - Streams not found');
        }

        $result['successData']['totalSw6MappedStreamConnections'] = count($mappedStreams);

        $entriesWithStreamUpdate = [];
        $toUpdateImportId = [];
        if (count($lexiconEntriesFromSw6) > 0) {
            // bereits vorhandene Einträge ggf. aussortieren
            foreach ($lexiconEntriesFromSw6 as $entry) {
                $delIndex = array_search(strtolower($entry['keyword']), $lexiconKeywords);

                if ($delIndex === false) {
                    $delIndex = array_search($entry['import_id'], $lexiconIds);
                }

                if ($delIndex !== false) {
                    if ($entry['import_id'] === null) {
                        $toUpdateImportId[] = ['id' => Uuid::fromBytesToHex($entry['id']), 'importId' => $lexiconEntries[$delIndex]['id']];
                    }

                    if (!empty($streamConnection[$lexiconEntries[$delIndex]['id']]) && empty($entry['product_stream_id']) && !empty($mappedStreams[$streamConnection[$lexiconEntries[$delIndex]['id']]])) {
                        // Stream in SW5 zugewiesen, aber in SW 6 nicht
                        $lexiconEntries[$delIndex]['id'] = Uuid::fromBytesToHex($entry['id']);
                        $lexiconEntries[$delIndex]['import_id'] = $entry['import_id'];
                        $entriesWithStreamUpdate[] = $lexiconEntries[$delIndex];
                    }

                    unset($lexiconEntries[$delIndex]);
                    unset($lexiconKeywords[$delIndex]);
                    unset($lexiconIds[$delIndex]);
                }
            }

            $lexiconEntries = array_values($lexiconEntries);
        }

        $lexiconHelper->getLexiconEntryRepository()->upsert($toUpdateImportId, $context);

        // gefilterte Einträge anlegen
        $toCreateLexiconEntries = [];
        $countSw6StreamsConnected = 0;
        $countNotCreatable = 0;
        foreach ($lexiconEntries as $lexiconItem) {
            $toCreateLexiconEntryTemp = $lexiconItem;

            $toCreateLexiconEntryTemp['importId'] = $toCreateLexiconEntryTemp['id'];

            $newId = Uuid::randomHex();
            $toCreateLexiconEntryTemp['id'] = $newId;

            // der Wert 24 wurde vom CrossSelling von SW übernommen
            $toCreateLexiconEntryTemp['productLimit'] = 24;
            $toCreateLexiconEntryTemp['impressions'] = (int) $toCreateLexiconEntryTemp['impressions'];
            $toCreateLexiconEntryTemp['productLayout'] = 'standard';

            switch ($toCreateLexiconEntryTemp['template']) {
                case 'article_listing_1col.tpl':
                    $toCreateLexiconEntryTemp['productTemplate'] = 'listing_1col';
                    break;
                case 'article_listing_2col.tpl':
                    $toCreateLexiconEntryTemp['productTemplate'] = 'listing_2col';
                    break;
                case 'slider.tpl':
                    $toCreateLexiconEntryTemp['productTemplate'] = 'slider';
                    break;
                case 'article_listing_3col.tpl':
                case 'article_listing_4col.tpl':
                default:
                    $toCreateLexiconEntryTemp['productTemplate'] = 'listing_3col';
                    break;
            }

            if (!empty($streamConnection[$toCreateLexiconEntryTemp['importId']]) && !empty($mappedStreams[$streamConnection[$toCreateLexiconEntryTemp['importId']]])) {
                $toCreateLexiconEntryTemp['productStreamId'] = $mappedStreams[$streamConnection[$toCreateLexiconEntryTemp['importId']]];
                $countSw6StreamsConnected++;
            } else if (!empty($streamConnection[$toCreateLexiconEntryTemp['importId']])) {
                $countNotCreatable++;
            }

            $toCreateLexiconEntries[] = $toCreateLexiconEntryTemp;
        }

        $lexiconHelper->getLexiconEntryRepository()->upsert($toCreateLexiconEntries, $context);

        $toUpdateLexiconEntries = [];
        foreach ($entriesWithStreamUpdate as $entryWithStreamUpdate) {
            $toUpdateLexiconEntries[] = ['id' => $entryWithStreamUpdate['id'], 'productStreamId' => $mappedStreams[$streamConnection[$entryWithStreamUpdate['import_id']]]];
        }

        $lexiconHelper->getLexiconEntryRepository()->upsert($toUpdateLexiconEntries, $context);

        $result['successData']['createdLexiconEntries'] = count($toCreateLexiconEntries);
        $result['successData']['countSw6StreamsConnected'] = $countSw6StreamsConnected;
        $result['successData']['countNotCreatableStreams'] = $countNotCreatable;
        $result['successData']['countUpdatedStreams'] = count($toUpdateLexiconEntries);

        $result['success'] = true;

        $result['msg'] = 'cbax-lexicon.config.notification.successImportData';

        return $result;
    }

    public function getSw5Data($result, $route, $start, $limit, $type) : array
    {
        $result['error'] = true;
        $result['dataType'] = 'cbax-lexicon.config.' . $type;

        $getQuery = '';
        $callUrl = $this->baseUrl . $route . $this->urlQuery;

        // Firmen holen
        try {
            $callResultSw5Data = $this->call($callUrl . '&start=' . $start . '&limit=' . $limit, $getQuery, $this->config);
        } catch (\Exception $error) {
            if (stripos($error->getMessage(), 'Could not resolve host') !== false) {
                $result['msg'] = 'cbax-lexicon.config.notification.errorResolveHost';
                $result['errorData'] = $callUrl;
            } else {
                $result['msg'] = $error->getMessage();
            }

            $traceDetails = $this->buildExceptionArray($error);

            $result['traceDetails'] = $traceDetails;

            return $result;
        }

        if (gettype($callResultSw5Data) === 'array') {
            if (isset($callResultSw5Data['Response']['Detail']) && stripos($callResultSw5Data['Response']['Detail'], 'forbidden') !== false) {
                $result['msg'] = 'cbax-lexicon.config.notification.errorCronKey';
            } else {
                $result['msg'] = 'cbax-lexicon.config.notification.errorCallResultSw5Data';
            }

            return $result;
        }

        $sw5Data = json_decode($callResultSw5Data, true);

        if ($sw5Data === null || !isset($sw5Data['data'])) {
            $result['msg'] = 'cbax-lexicon.config.notification.errorCallResultSw5Data';
            return $result;
        }

        return $sw5Data;
    }

    /**
     * Anmerkung - aufgrund der möglichen, hohen Datenmengen werden Variablen nach gebrauch unsetted
     */
    public function importProductAssignments($start, $limit, $lexiconHelper, $context)
    {
        $result = ['success' => false, 'msg' => 'cbax-lexicon.config.notification.emptySettings',
            'successData' => ['countExistingLexiconProducts' => 0, 'createdLexiconProducts' => 0, 'countNotCreatableProduct' => 0]
        ];

        $this->setPhpLimits();

        $this->config = $this->systemConfigService->get(Database::CONFIG_PATH);

        // Einstellungen prüfen
        if (empty($this->config['urlImport']) || empty($this->config['cronKeyImport'])) {
            return $result;
        }

        if (stripos($this->config['urlImport'], 'http://') === false && stripos($this->config['urlImport'], 'https://') === false) {
            $this->config['urlImport'] = 'http://' . $this->config['urlImport'];
        }

        $this->baseUrl = $this->config['urlImport'] . '/backend/LexiconMigration/';
        $this->urlQuery = '?key=' . $this->config['cronKeyImport'];

        $route = 'getArticleAssignments';
        $type = 'lexiconProducts';

        $lexiconProductsComplete = $this->getSw5Data($result, $route, $start, $limit, $type);

        if (!empty($lexiconProductsComplete['error'])) {
            return $lexiconProductsComplete;
        }

        $lexiconProducts = $lexiconProductsComplete['data'];

        if ($lexiconProductsComplete['total'] === 0) {
            // keine Daten gefunden
            $result['msg'] = 'cbax-lexicon.config.notification.errorNoSw5Entries';
            return $result;
        }

        $result['successData']['totalLexiconProducts'] = (int) $lexiconProductsComplete['total'];

        unset($lexiconProductsComplete);

        $lexiconArticle = [];
        foreach ($lexiconProducts as $lexiconProduct) {
            $lexiconArticle[$lexiconProduct['articleID']] = $lexiconProduct['ordernumber'];
        }

        $result['successData']['totalSw5Products'] = count($lexiconArticle);

        $sqlSw6Products = 'SELECT id, parent_id from product WHERE product_number IN ("' . implode('","', $lexiconArticle) . '")';

        $productsFromSw6 = $this->connection->executeQuery($sqlSw6Products)->fetchAllAssociative();

        $sw6ProductIds = [];
        foreach ($productsFromSw6 as $productFromSw6) {
            if (empty($productFromSw6['parent_id'])) {
                $sw6ProductIds[] = Uuid::fromBytesToHex($productFromSw6['id']);
            } else {
                $sw6ProductIds[] = Uuid::fromBytesToHex($productFromSw6['parent_id']);
            }
        }

        // ggf. vorhandene Einträge suchen
        // ROW_NUMBER() OVER () nummeriert die Treffer zum Zählen
        $sql = '
            SELECT DISTINCT SQL_CALC_FOUND_ROWS ROW_NUMBER() OVER () AS row_num, `cbax_lexicon_entry_id`, `product_id`, `product_number` FROM `cbax_lexicon_product` lp
            INNER JOIN product p ON lp.product_id = p.id
            WHERE HEX(product_id) IN (
                "' . implode('","', $sw6ProductIds) . '"
            )
            GROUP BY product_number
        ';

        $lexiconProductsFromSw6 = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $foundProducts = [];
        $notFoundProducts = array_flip(array_values($lexiconArticle));
        foreach ($lexiconProductsFromSw6 as $lexiconProduct) {
            $foundProducts[$lexiconProduct['product_id']] = true;

            unset($notFoundProducts[$lexiconProduct['product_number']]);
            unset($notFoundProducts[$lexiconProduct['product_number'].'M']);
            if (substr($lexiconProduct['product_number'], -1, 1) === 'M') {
                unset($notFoundProducts[substr($lexiconProduct['product_number'], 0, -1)]);
            }
        }

        if (count($notFoundProducts) > 0) {
            $lexiconHelper->doShopwareLog(array_keys($notFoundProducts), 'Products not found / Produkte nicht gefunden', $context, 'Info', 'Lexikon - Products not found');
        }

        $result['successData']['totalSw6ProductsFound'] = count($foundProducts);

        $sqlCount= "SELECT FOUND_ROWS() as count";
        $countResult = $this->connection->executeQuery($sqlCount)->fetchAssociative();

        $result['successData']['countExistingLexiconProducts'] = (int) $countResult['count'];

        // importierte Lexikon-Einträge suchen
        $sql = '
            SELECT `id`, `import_id` FROM `cbax_lexicon_entry`
            WHERE import_id is not null
        ';

        $sw6lexiconEntries = $this->connection->executeQuery($sql)->fetchAllAssociative();

        // importierte nach import_id mappen
        $orderedLexiconEntries = [];
        foreach ($sw6lexiconEntries as $sw6lexiconEntry) {
            $orderedLexiconEntries[$sw6lexiconEntry['import_id']] = $sw6lexiconEntry['id'];
        }

        unset($sw6lexiconEntries);

        // vorhandene entfernen
        $countAlreadyExistingAssignments = 0;
        foreach ($lexiconProducts as $index => $lexiconProduct) {
            // Einträge mit gleicher Produktnummer filtern
            $tempFilteredSwLexiconProducts = array_filter($lexiconProductsFromSw6, function ($item) use ($lexiconProduct) {
                return $item['product_number'] === $lexiconProduct['ordernumber'];
            });
            foreach ($tempFilteredSwLexiconProducts as $tempFilteredSwLexiconProduct) {
                if ($orderedLexiconEntries[$lexiconProduct['lexiconID']] === $tempFilteredSwLexiconProduct['cbax_lexicon_entry_id']) {
                    unset($lexiconProducts[$index]);
                    $countAlreadyExistingAssignments++;
                }
            }
        }

        $result['successData']['countAlreadyExistingAssignments'] = $countAlreadyExistingAssignments;

        // fehlende SW 5 Produktnummer
        $lexiconArticle = [];
        $migratedLexiconArticle = [];
        foreach ($lexiconProducts as $lexiconProduct) {
            $lexiconArticle[$lexiconProduct['articleID']] = $lexiconProduct['ordernumber'];
            $migratedLexiconArticle[$lexiconProduct['articleID']] = $lexiconProduct['ordernumber'] . 'M';
        }

        if (count($lexiconArticle) > 0) {
            // übrige Produkte versuchen zu identifizieren
            $sql = '
                SELECT `id`, `product_number`, `parent_id` FROM `product`
                WHERE product_number IN ("' . implode('","', $lexiconArticle) . '")
                OR product_number IN ("' . implode('","', $migratedLexiconArticle) . '")
            ';

            $productsFromSw6 = $this->connection->executeQuery($sql)->fetchAllAssociative();

            $mappedProducts = [];
            $productIds = [];
            foreach ($productsFromSw6 as $product) {
                $articleId = array_search($product['product_number'], $lexiconArticle);
                if ($articleId === false) {
                    $articleId = array_search($product['product_number'], $migratedLexiconArticle);
                }
                if ($articleId !== false) {
                    $productId = Uuid::fromBytesToHex($product['id']);

                    $productIds[$productId] = $productId;
                    if (!empty($product['parent_id'])) {
                        $productIds[$productId] = Uuid::fromBytesToHex($product['parent_id']);
                    }

                    $mappedProducts[$articleId] = $productId;
                }
            }

            unset($productsFromSw6);
            unset($lexiconArticle);

            $toCreateLexiconProducts = [];
            $toConnectProducts = [];
            $countNotCreatable = 0;
            foreach ($lexiconProducts as $lexiconProduct) {
                if (!empty($mappedProducts[$lexiconProduct['articleID']]) && !empty($orderedLexiconEntries[$lexiconProduct['lexiconID']])) {
                    $lexId = Uuid::fromBytesToHex($orderedLexiconEntries[$lexiconProduct['lexiconID']]);
                    $toCreateLexiconProducts[] = [
                        'productId' => $productIds[$mappedProducts[$lexiconProduct['articleID']]],
                        'cbaxLexiconEntryId' => $lexId
                    ];

                    $toConnectProducts[] = [
                        'id' => $mappedProducts[$lexiconProduct['articleID']],
                        'cbaxLexiconEntry' => $productIds[$mappedProducts[$lexiconProduct['articleID']]]
                    ];
                } else {
                    $countNotCreatable++;
                }
            }

            $lexiconHelper->getProductRepository()->update($toConnectProducts, $context);

            $this->lexiconProductRepository->upsert($toCreateLexiconProducts, $context);

            $result['successData']['createdLexiconProducts'] = count($toCreateLexiconProducts);
            $result['successData']['countNotCreatableProduct'] = $countNotCreatable;
        }

        $result['success'] = true;

        $result['msg'] = 'cbax-lexicon.config.notification.successImportProductAssignments';

        return $result;
    }

    /**
     * Anmerkung - aufgrund der möglichen, hohen Datenmengen werden Variablen nach gebrauch unsetted
     */
    public function importShopAssignments($start, $limit, $lexiconHelper, $context)
    {
        $result = ['success' => false, 'msg' => 'cbax-lexicon.config.notification.emptySettings',
            'successData' => ['countExistingLexiconProducts' => 0, 'createdLexiconProducts' => 0, 'countNotCreatableSalesChannel' => 0]
        ];

        $this->setPhpLimits();

        $this->config = $this->systemConfigService->get(Database::CONFIG_PATH);

        // Einstellungen prüfen
        if (empty($this->config['urlImport']) || empty($this->config['cronKeyImport'])) {
            return $result;
        }

        if (stripos($this->config['urlImport'], 'http://') === false && stripos($this->config['urlImport'], 'https://') === false) {
            $this->config['urlImport'] = 'http://' . $this->config['urlImport'];
        }

        $this->baseUrl = $this->config['urlImport'] . '/backend/LexiconMigration/';
        $this->urlQuery = '?key=' . $this->config['cronKeyImport'];

        $route = 'getShopAssignments';
        $type = 'lexiconShop';

        $lexiconShopsComplete = $this->getSw5Data($result, $route, $start, $limit, $type);

        if (!empty($lexiconShopsComplete['error'])) {
            return $lexiconShopsComplete;
        }

        $lexiconShops = $lexiconShopsComplete['data'];

        if ($lexiconShopsComplete['total'] === 0) {
            // keine Daten gefunden
            $result['msg'] = 'cbax-lexicon.config.notification.errorNoSw5Entries';
            return $result;
        }

        $result['successData']['totalLexiconShops'] = (int) $lexiconShopsComplete['total'];

        $sw5Shops = $lexiconShopsComplete['shops'];

        $result['successData']['totalSw5Shops'] = count($sw5Shops);

        unset($lexiconShopsComplete);

        $lexiconEntryIds = [];
        foreach ($lexiconShops as $lexiconShop) {
            $lexiconEntryIds[$lexiconShop['lexiconID']] = $lexiconShop['lexiconID'];
        }

        // vorhandene Einträge suchen
        $sql = '
            SELECT le.`id`, `import_id` FROM `cbax_lexicon_entry` le
            WHERE import_id IN ("' . implode('","', $lexiconEntryIds) . '")
        ';

        $lexiconEntriesFromSw6 = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $mappedEntries = [];
        foreach ($lexiconEntriesFromSw6 as $entry) {
            $mappedEntries[$entry['import_id']] = Uuid::fromBytesToHex($entry['id']);
        }

        $shopHosts = [];
        $shopNames = [];
        $hostPrefixes = ['', 'http://', 'https://', 'www.', 'http://wwww.', 'https://wwww.'];
        foreach ($sw5Shops as $sw5Shop) {
            $shopNames[] = $sw5Shop['name'];

            foreach ($hostPrefixes as $hostPrefix) {
                $shopHosts[] = $hostPrefix . $sw5Shop['host'];
            }
        }

        $sql = '
            SELECT s.`id`, `name` FROM `sales_channel` s
            INNER JOIN `sales_channel_translation` st ON `s`.`id` = `st`.`sales_channel_id`
            WHERE `name` IN ("' . implode('","', $shopNames) . '") and type_id = 0x'. Defaults::SALES_CHANNEL_TYPE_STOREFRONT .'
        ';

        $resultSw6SalesChannelNames = $this->connection->executeQuery($sql)->fetchAllAssociative();

        unset($shopNames);

        $mappedNames = [];
        foreach ($resultSw6SalesChannelNames as $resultSw6SalesChannelName) {
            $mappedNames[Uuid::fromBytesToHex($resultSw6SalesChannelName['id'])] = strtolower($resultSw6SalesChannelName['name']);
        }

        $sql = '
            SELECT s.`id`, `url` FROM `sales_channel` s
            INNER JOIN `sales_channel_domain` sd ON `s`.`id` = `sd`.`sales_channel_id`
            WHERE `url` IN ("' . implode('","', $shopHosts) . '")
            GROUP BY `s`.`id`
            ORDER BY s.`id`
        ';

        $resultSw6SalesChannelHosts = $this->connection->executeQuery($sql)->fetchAllAssociative();

        unset($shopHosts);

        $mappedHosts = [];
        foreach ($resultSw6SalesChannelHosts as $resultSw6SalesChannelHost) {
            $mappedHosts[Uuid::fromBytesToHex($resultSw6SalesChannelHost['id'])] = strtolower($resultSw6SalesChannelHost['url']);
        }

        $mappedShops = [];
        $missingShops = [];
        foreach ($sw5Shops as $sw5Shop) {
            $found = false;
            foreach ($hostPrefixes as $hostPrefix) {
                if (in_array(strtolower($hostPrefix . $sw5Shop['host']), $mappedHosts)) {
                    $mappedShops[$sw5Shop['id']] = array_search(strtolower($hostPrefix . $sw5Shop['host']), $mappedHosts);
                    $found = true;
                }
            }

            if (in_array(strtolower($sw5Shop['name']), $mappedNames)) {
                $mappedShops[$sw5Shop['id']] = array_search(strtolower($sw5Shop['name']), $mappedNames);
                $found = true;
            }

            if ($found === false) {
                $missingShops[] = $sw5Shop['name'];
            }
        }

        $result['successData']['totalSw6ShopsFound'] = count($mappedShops);

        if (count($missingShops) > 0) {
            $lexiconHelper->doShopwareLog(array_values($missingShops), 'Shops not found / Shops nicht gefunden', $context, 'Info', 'Lexikon - Shops not found');
        }

        unset($mappedNames);
        unset($mappedHosts);

        $toCreateAssignments = [];
        $countNotCreatable = 0;
        foreach ($lexiconShops as $lexiconShop) {
            if (!empty($mappedEntries[$lexiconShop['lexiconID']]) && !empty($mappedShops[$lexiconShop['shopID']])) {
                $toCreateAssignments[] = ['cbaxLexiconEntryId' => $mappedEntries[$lexiconShop['lexiconID']], 'salesChannelId' => $mappedShops[$lexiconShop['shopID']]];
            } else {
                $countNotCreatable++;
            }
        }

        if (count($toCreateAssignments) > 0) {
            $sql = 'SELECT cbax_lexicon_entry_id, sales_channel_id FROM cbax_lexicon_sales_channel WHERE 1=1';
            foreach ($toCreateAssignments as $index => $toCreateAssignment) {
                if ($index === array_key_first($toCreateAssignments)) {
                    $sql .= ' AND ';
                }
                $sql .= '(cbax_lexicon_entry_id = 0x' . $toCreateAssignment['cbaxLexiconEntryId'] . ' AND sales_channel_id = 0x' . $toCreateAssignment['salesChannelId'] . ')';
                if ($index !== array_key_last($toCreateAssignments)) {
                    $sql .= ' OR ';
                }
            }

            $resultSw6LexiconSalesChannels = $this->connection->executeQuery($sql)->fetchAllAssociative();
        } else {
            $resultSw6LexiconSalesChannels = [];
        }

        $result['successData']['countAlreadyExistingShopAssignments'] = count($resultSw6LexiconSalesChannels);

        foreach ($resultSw6LexiconSalesChannels as $resultSw6LexiconSalesChannel) {
            foreach ($toCreateAssignments as $index => $toCreateAssignment) {
                if ($toCreateAssignment['cbaxLexiconEntryId'] === Uuid::fromBytesToHex($resultSw6LexiconSalesChannel['cbax_lexicon_entry_id']) &&
                    $toCreateAssignment['salesChannelId'] === Uuid::fromBytesToHex($resultSw6LexiconSalesChannel['sales_channel_id'])
                ) {
                    unset($toCreateAssignments[$index]);
                    continue 2;
                }
            }
        }

        $toCreateAssignments = array_values($toCreateAssignments);

        $this->lexiconSalesChannelRepository->upsert($toCreateAssignments, $context);

        $result['successData']['createdLexiconSalesChannel'] = count($toCreateAssignments);
        $result['successData']['countNotCreatableSalesChannel'] = $countNotCreatable;

        $result['success'] = true;

        $result['msg'] = 'cbax-lexicon.config.notification.successImportShopAssignments';

        return $result;
    }

    public function setPhpLimits($memoryLimit = '4096M', $executionTime = '2000')
    {
        $memLimit = ini_get('memory_limit');
        $maxExecTime = (int) ini_get('max_execution_time');

        // Cases abfangen 'B', 'G', 'K'
        if (str_contains($memLimit, 'M')) {
            $memLimit = (int) str_replace('M', '', $memLimit);
        } elseif (str_contains($memLimit, 'G')) {
            $memLimit = (int) str_replace('G', '', $memLimit);
            $memLimit = $memLimit * 1024;
        } elseif (str_contains($memLimit, 'K')) {
            $memLimit = (int) str_replace('K', '', $memLimit);
            $memLimit = $memLimit / 1024;
        } else {
            $memLimit = (int) str_replace('B', '', $memLimit);
            $memLimit = $memLimit / 1024 / 1024;
        }

        if ($memLimit < 4096) {
            ini_set('memory_limit', $memoryLimit);
        }

        if ($maxExecTime < 2000) {
            ini_set('max_execution_time', $executionTime);
        }
    }

    public function buildExceptionArray($exception) : array {
        $return = [];
        $return['exception'] = [
            'Message' => $exception->getMessage(),
            'Previous' => $exception->getPrevious(),
            'Code' => $exception->getCode(),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            'Trace' => $exception->getTrace(),
            'TraceString' => $exception->getTraceAsString()
        ];

        return $return;
    }

    public function call($baseUrl, $query, $settings, $method = 'GET', $headers = [], $formParams = null, $options = null)
    {
        return GuzzleClient::call($baseUrl, $query, $settings, $headers, $method, $formParams, $options);
    }
}