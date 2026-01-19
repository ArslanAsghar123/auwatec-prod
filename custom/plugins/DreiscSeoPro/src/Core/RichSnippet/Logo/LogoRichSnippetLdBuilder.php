<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\Logo;

use DreiscSeoPro\Core\Content\Media\MediaRepository;
use DreiscSeoPro\Core\Content\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class LogoRichSnippetLdBuilder implements LogoRichSnippetLdBuilderInterface
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function build(LogoRichSnippetLdBuilderStruct $logoRichSnippetLdBuilderStruct): array
    {
        $logoSettings = $logoRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getLogo();

        /** Abort, if the transfer of the logo data is inactive */
        if (true !== $logoSettings->getGeneral()->isActive()) {
            return [];
        }

        /** Base fields */
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization'
        ];

        /** Set the logo url if exists */
        $logo = $this->fetchLogo($logoRichSnippetLdBuilderStruct);
        if(!empty($logo)) {
            $ld['logo'] = $logo;
        }

        /** Set the url if exists */
        if(!empty($logoSettings->getGeneral()->getUrl())) {
            $ld['url'] = $logoSettings->getGeneral()->getUrl();
        }

        return $ld;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    private function fetchLogo(LogoRichSnippetLdBuilderStruct $logoRichSnippetLdBuilderStruct): ?string
    {
        $logoSettings = $logoRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getLogo();

        $mediaId = $logoSettings->getGeneral()->getLogo();

        /** Abort, if no media id is set */
        if(empty($mediaId)) {
            return null;
        }

        /** Fetch the media info. Abort, if no media was found */
        $mediaEntity = $this->mediaRepository->get($mediaId);
        if (null === $mediaEntity) {
            return null;
        }

        /** Return the logo url */
        return $mediaEntity->getUrl();
    }
}
