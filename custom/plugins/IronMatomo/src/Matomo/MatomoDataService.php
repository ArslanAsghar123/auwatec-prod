<?php declare(strict_types=1);

namespace IronMatomo\Matomo;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class MatomoDataService
 *
 * @package IronMatomo\Matomo
 */
class MatomoDataService
{

    /** @var SystemConfigService */
    private $systemConfigService;

    /** * @var EntityRepository */
    protected $domainRepository;

    /**
     * @param EntityRepository $domainRepository
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(
        EntityRepository $domainRepository,
        SystemConfigService $systemConfigService
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @param string $salesChannelId
     * @return string|null
     */
    private function dataFromSalesChannel(string $salesChannelId): ?string
    {
        $matomoUrl = $this->systemConfigService->get('IronMatomo.config.matomoUrl', $salesChannelId);
        $matomoSiteId = $this->systemConfigService->get('IronMatomo.config.siteId', $salesChannelId);
        if (empty($matomoUrl) || empty($matomoSiteId)) {
            return null;
        }
        return $salesChannelId;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return MatomoData
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function getMatomoData(SalesChannelContext $salesChannelContext)
    {
        $matomoData = new MatomoData();
        $fromSalesChannelId = $this->dataFromSalesChannel($salesChannelContext->getSalesChannel()->getId());
        $matomoUrl = $this->systemConfigService->get('IronMatomo.config.matomoUrl', $fromSalesChannelId);
        $matomoFile = $this->systemConfigService->get('IronMatomo.config.matomoFile', $fromSalesChannelId);
        $matomoScript = $this->systemConfigService->get('IronMatomo.config.matomoScript', $fromSalesChannelId);
        $matomoSiteId = $this->systemConfigService->get('IronMatomo.config.siteId', $fromSalesChannelId);
        if (empty($matomoUrl) || empty($matomoSiteId)) {
            return $matomoData;
        }
        $matomoSiteId = trim($matomoSiteId);

        // Filter URL
        $matomoUrl = rtrim($matomoUrl, '/');
        if (strpos($matomoUrl, 'http') === false) {
            $matomoUrl = '//' . $matomoUrl;
        }
        $matomoUrl .= '/';

        $cookieDomain = '';
        if ($this->systemConfigService->get('IronMatomo.config.allowSubdomain', $fromSalesChannelId)) {
            if (!empty($this->systemConfigService->get('IronMatomo.config.hostDomain', $fromSalesChannelId))) {
                $cookieDomain = "*." . $this->systemConfigService->get('IronMatomo.config.hostDomain', $fromSalesChannelId);
            } else {
                $cookieDomain = "*." . $this->getHostnameFromSalesChannel($salesChannelContext);
            }
        }

        $matomoData->setMatomoFile(empty((string)$matomoFile) ? 'matomo.php' : (string)$matomoFile);
        $matomoData->setMatomoScript(empty((string)$matomoScript) ? 'matomo.js' : (string)$matomoScript);

        $matomoData->setActive((!empty($matomoUrl) && !empty($matomoSiteId)));
        $matomoData->setMatomoUrl($matomoUrl);
        $matomoData->setSiteId((int)$matomoSiteId);
        $matomoData->setGroupByDomain((bool)$this->systemConfigService->get('IronMatomo.config.groupByDomain', $fromSalesChannelId));
        $matomoData->setDisableCookies((bool)$this->systemConfigService->get('IronMatomo.config.disableCookies', $fromSalesChannelId));
        $matomoData->setRequireCookieConsent((bool)$this->systemConfigService->get('IronMatomo.config.requireCookieConsent', $fromSalesChannelId));
        $matomoData->setConversionFirstReferrer((bool)$this->systemConfigService->get('IronMatomo.config.conversionFirstReferrer', $fromSalesChannelId));
        $matomoData->setCookieName((string)$this->systemConfigService->get('IronMatomo.config.cookieName', $fromSalesChannelId));
        $matomoData->setCookieValue((string)$this->systemConfigService->get('IronMatomo.config.cookieValue', $fromSalesChannelId));
        $matomoData->setCookieValueAsRegex((bool)$this->systemConfigService->get('IronMatomo.config.cookieValueAsRegex', $fromSalesChannelId));
        $matomoData->setShopHostname($this->getHostnameFromSalesChannel($salesChannelContext));
        $matomoData->setUserTrack($this->getUserInfoToTrack($salesChannelContext, $fromSalesChannelId));
        $matomoData->setHostDomain((string)$this->systemConfigService->get('IronMatomo.config.hostDomain', $fromSalesChannelId));
        $matomoData->setCookieDomain($cookieDomain);
        $matomoData->setStartTracking((int)$this->systemConfigService->get('IronMatomo.config.startTracking', $fromSalesChannelId));
        $matomoData->setUseOwnMatomoScript((bool)$this->systemConfigService->get('IronMatomo.config.useOwnMatomoScript', $fromSalesChannelId));
        $matomoData->setUseOwnMatomoScriptCode((string)$this->systemConfigService->get('IronMatomo.config.useOwnMatomoScriptCode', $fromSalesChannelId));

        return $matomoData;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return string
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    private function getHostnameFromSalesChannel(SalesChannelContext $salesChannelContext): string
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        $criteria->setLimit(1);
        /** @var SalesChannelDomainEntity $domain */
        $domain = $this->domainRepository
            ->search($criteria, $salesChannelContext->getContext())
            ->first();
        $urlParts = \parse_url($domain->getUrl());
        return $urlParts['host'] ?? '';
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param string|null $fromSalesChannelId
     * @return bool|mixed
     */
    private function getUserInfoToTrack(SalesChannelContext $salesChannelContext, ?string $fromSalesChannelId): string
    {
        $userTrack = '';
        $userData = $salesChannelContext->getCustomer();
        if ($userData) {
            switch ($this->systemConfigService->get('IronMatomo.config.userId', $fromSalesChannelId)) {
                case 1:
                    $userTrack = $userData->getId();
                    break;
                case 2:
                    $userTrack = $userData->getCustomerNumber();
                    break;
                case 3:
                    $userTrack = $userData->getEmail();
                    break;
                default:
                    $userTrack = '';
            }
        }
        return $userTrack;
    }
}
