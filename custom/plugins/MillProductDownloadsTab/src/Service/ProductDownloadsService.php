<?php declare(strict_types=1);

namespace Mill\ProductDownloadsTab\Service;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\System\CustomField\CustomFieldEntity;

/**
 * Class ProductDownloadsService
 */

class ProductDownloadsService
{

    private EntityRepository $mediaRepository;

    private EntityRepository $customFieldRepository;

    /**
     * ProductDownloadsService constructor.
     *
     * @param EntityRepository $mediaRepository
     * @param EntityRepository $customFieldRepository
     */

    public function __construct(EntityRepository $mediaRepository, EntityRepository $customFieldRepository)
    {

        $this->mediaRepository = $mediaRepository;
        $this->customFieldRepository = $customFieldRepository;

    }

    /**
     * Main-function to get a list of product media downloads
     *
     * @param Context $context
     * @param ProductEntity $product
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     */

    public function getProductDownloads($context, $product): array
    {

        $mediaFiles = [];

        $customFieldNames = $this->getProductDownloadCustomFields($context);

        if (!empty($customFieldNames)) {

            $customFields = $product->getCustomFields();

            if (!empty($customFields)) {

                $mediaIds = $this->getCustomFieldIdsByNames($product, $customFieldNames);

                if (!empty($mediaIds)) {

                    $mediaFiles = $this->getMediaByIds($context, $mediaIds);

                }

            }

        }

        return $mediaFiles;

    }

    /**
     * @param ProductEntity $product
     * @param array $customFieldNames
     *
     * @return array
     */

    private function getCustomFieldIdsByNames($product, $customFieldNames): array
    {

        $ids = [];

        $customFields = $product->getCustomFields();

        if (!empty($customFields)) {

            foreach ($customFieldNames as $customFieldName) {

                if (!empty($customFields[$customFieldName])) {

                    $ids[] = $customFields[$customFieldName];

                }

            }

        }

        return $ids;

    }

    /**
     * Helper-function to get media data by media ids.
     *
     * @param Context $context
     * @param array $ids
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     */

    private function getMediaByIds($context, $ids): array
    {

        $files = [];

        $criteria = new Criteria($ids);

        $searchResult = $this->mediaRepository->search($criteria, $context);

        if (!empty($searchResult)) {

            $mediaFiles = $searchResult->getElements();

            if (!empty($mediaFiles)) {

                /**
                 * @var MediaEntity $mediaFile
                 */

                foreach ($mediaFiles as $mediaFile) {

                    $downloadTitle = $mediaFile->getTitle();

                    if (empty($downloadTitle)) {

                        $downloadTitle = $mediaFile->getFileName();

                    }

                    $files[] = [
                        'id' => $mediaFile->getId(),
                        'title' => $downloadTitle,
                        'url' => $mediaFile->getUrl(),
                        'fileExtension' => $mediaFile->getFileExtension()
                    ];

                }

            }

        }

        return $files;

    }

    /**
     * Helper-function to get plugin custom field names.
     *
     * @param Context $context
     *
     * @return array
     *
     * @throws InconsistentCriteriaIdsException
     */

    private function getProductDownloadCustomFields($context): array
    {

        $customFieldNames = [];

        $criteria = new Criteria();

        $criteria->addFilter(
            new ContainsFilter('name', 'mill_product_download')
        );

        $searchResult = $this->customFieldRepository->search($criteria, $context);

        if (!empty($searchResult)) {

            $customFields = $searchResult->getElements();

            if (!empty($customFields)) {

                /**
                 * @var CustomFieldEntity $customField
                 */

                foreach ($customFields as $customField) {

                    $customFieldNames[] = $customField->getName();

                }

                natsort($customFieldNames);

            }

        }

        return $customFieldNames;

    }

}