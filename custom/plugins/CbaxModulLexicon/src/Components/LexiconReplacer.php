<?php declare(strict_types = 1);

namespace Cbax\ModulLexicon\Components;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Defaults;

use Cbax\ModulLexicon\Core\Content\Events\CbaxLexiconEntriesLoadedEvent;

class LexiconReplacer
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection
    )
    {

    }

    public function getReplaceText(
        ?string $text,
        string $salesChannelId,
        string $shopUrl,
        Context $context,
        array $config,
        ?string $lexicon_id = null
    ): string
    {
        if (empty($text)) {
            return '';
        }

        if (empty($config['active'])) {
            return $text;
        }

        $languageId = $context->getLanguageId();
        $lexiconEntries = $this->getLexiconEntries($languageId, $salesChannelId);

        $event = $this->eventDispatcher->dispatch(new CbaxLexiconEntriesLoadedEvent($lexiconEntries));
        $lexiconEntries = $event->getEntries();

        if (empty($lexiconEntries)) {
            return $text;
        }

        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');
        // Nur ganze Wörter ersetzen (\w ersetzt [a-zäöüß-])
        $replaceMode = ($config['replaceComplete']) ? '\w' : '\b\B';
        // keine Buchstaben, Umlaute oder ß als vorheriges Zeichen, kein <a ...> Tag vorne
        $regExPrefix = "/(?!<a[^>]*>)(?!<img[^>]*>)(?<!$replaceMode)(";
        // nicht innerhalb HTML Tags (<>) und keine Buchstaben, Umlaute oder ß als nachfolgendes Zeichen, kein </a> Tag hinten
        $regExSuffix = ")(?![^<]*>)(?![^<]*<\/a>)(?!$replaceMode)/";
        // /umi (u = utf8 support; m = multiline; i = case insensitive)
        $regExFlags = 'umi';

        // Alle SEO-URLs holen
        if ($config['linkHandling'] == 'modal' && !empty($config['showSeo'])) {
            $seoUrls = $this->getSeoUrls($languageId, $salesChannelId);
        } else {
            $seoUrls = [];
        }

        // Suchmuster und Ersetzungen erstellen
        $suchmuster = [];
        $ersetzungen = [];

        foreach ($lexiconEntries as $entry) {
            // den eigenen Eintrag in der Detailseite überspringen und nur andere Einträge prüfen
            if ($lexicon_id !== null && $entry['id'] === $lexicon_id) {
                continue;
            }

            // Bei Eingabe mehrerer Keywords in einem Feld diese splitten und Array neu erstellen
            $getKeywords = explode('+',  $entry['keyword']);

            if (count($getKeywords) > 1) {
                foreach ($getKeywords as $keyword) {
                    if (!empty($keyword)) {
                        $suchmuster[] = $regExPrefix . preg_quote(trim($keyword), '/') . $regExSuffix . $regExFlags;
                        $ersetzungen[] = $this->getReplacement($shopUrl, $config, $entry['id'], trim($keyword), htmlspecialchars(strip_tags((string)$entry['description'])), $seoUrls);
                    }
                }
            } else {

                $suchmuster[] = $regExPrefix . preg_quote(trim($entry['keyword']), '/') . $regExSuffix . $regExFlags;
                $ersetzungen[] = $this->getReplacement($shopUrl, $config, $entry['id'], trim($entry['keyword']), htmlspecialchars(strip_tags((string)$entry['description'])), $seoUrls);
            }
        }

        // Einträge ersetzen
        $text1 = preg_replace($suchmuster, $ersetzungen, $text, !empty($config['replaceRepeat']) ? -1 : 1);
        if (empty($text1)) {
            return '';
        }
        // Marker wieder löschen
        $text = str_replace('></do-not-change>', '', $text1);

        return $text;
    }

    private function getLexiconEntries(string $languageId, string $salesChannelId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $query = $qb
            ->select([
                'LOWER(HEX(entries.id)) as id',
                'IF(trans1.keyword IS NOT NULL, trans1.keyword, trans2.keyword) as keyword',
                'IF(trans1.description IS NOT NULL, trans1.description, trans2.description) as description'
            ])
            ->from('`cbax_lexicon_entry`', 'entries')
            ->leftJoin(
                'entries',
                '`cbax_lexicon_entry_translation`',
                'trans1',
                'trans1.cbax_lexicon_entry_id = entries.id AND trans1.language_id = :languageId1')
            ->leftJoin(
                'entries',
                '`cbax_lexicon_entry_translation`',
                'trans2',
                'trans2.cbax_lexicon_entry_id = entries.id AND trans2.language_id = :languageId2')
            ->innerJoin(
                'entries',
                '`cbax_lexicon_sales_channel`',
                'salesChannels',
                'entries.id = salesChannels.cbax_lexicon_entry_id AND salesChannels.sales_channel_id = :salesChannelId'
            )
            ->andWhere('entries.date <= :nowDate')
            ->setParameters([
                'languageId1' => Uuid::fromHexToBytes($languageId),
                'languageId2' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'nowDate' => date('Y-m-d H:i:s'),
                'salesChannelId' => Uuid::fromHexToBytes($salesChannelId)
            ]);

        return $query->fetchAllAssociative();
    }

    private function getSeoUrls(string $languageId, string $salesChannelId): array
    {
        $sql = "SELECT path_info, seo_path_info FROM `seo_url`
                WHERE language_id = ?
                 AND sales_channel_id = ?
                 AND route_name = 'frontend.cbax.lexicon.detail'
                 AND is_canonical = 1
                 AND is_deleted = 0";

        return $this->connection->fetchAllKeyValue($sql, [Uuid::fromHexToBytes($languageId), Uuid::fromHexToBytes($salesChannelId)]);
    }

    private function getReplacement(
        string $shopUrl,
        array $config,
        string $id,
        string $keyword,
        string $description,
        array $seoUrls
    ): string
    {
        $link = '';
        $dataUrl = '';
        if ($config['linkHandling'] == 'modal') {
            //modal controller
            $dataUrl = $shopUrl . '/cbax/lexicon/modalInfo/' . $id;

            if (!empty($config['showSeo'])) {
                $path_info = '/cbax/lexicon/detail/' . $id;

                if (isset($seoUrls[$path_info])) {
                    $link = $shopUrl . '/' . $seoUrls[$path_info];
                } else {
                    $link = $shopUrl . $path_info;
                }
            }
        }

        // $0 ersetzt alle gefundenen Werte
        if ($config['linkHandling'] == 'tooltip') {
            $dataTemplate = "<div class='tooltip' role='tooltip'><div class='arrow'></div><div class='tooltip-inner cbax-lexicon-tooltip-inner'></div></div>";

            $ersetzung = '<a class="lexicon-tooltip cbax-lexicon-link" data-bs-toggle="tooltip" title="' .
                $description .
                '" data-bs-placement="top" data-bs-html="true" data-bs-template=></do-not-change>"' .
                $dataTemplate . '">$0</a>';

        } else {
            $ersetzung = '<span class="lexicon-modal"><a href="' . $link . '" title="' . $keyword . '"
                            data-ajax-modal="modal" data-original-title="' . $description . '" data-url="' . $dataUrl . '">$0</a></span>';
        }

        return $ersetzung;
    }

}
