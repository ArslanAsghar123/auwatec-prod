<?php declare(strict_types=1);

namespace IronMatomo\Matomo;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class MatomoData
 *
 * @package IronMatomo\Matomo
 */
class MatomoData extends Struct
{
    protected $active = false;
    protected $matomoUrl = '';
    protected $matomoFile = '';
    protected $matomoScript = '';
    protected $siteId = 0;
    protected $groupByDomain = false;
    protected $disableCookies = false;
    protected $requireCookieConsent = false;
    protected $conversionFirstReferrer = false;
    protected $cookieName = '';
    protected $cookieValue = '';
    protected $cookieValueAsRegex = false;
    protected $shopHostname = '';
    protected $userTrack = '';
    protected $hostDomain = '';
    protected $cookieDomain = '';
    protected $startTracking = 1;
    protected $useOwnMatomoScript = false;
    protected $useOwnMatomoScriptCode = '';

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getMatomoUrl(): string
    {
        return $this->matomoUrl;
    }

    /**
     * @param string $matomoUrl
     */
    public function setMatomoUrl(string $matomoUrl): void
    {
        $this->matomoUrl = $matomoUrl;
    }

    /**
     * @return string
     */
    public function getMatomoFile(): string
    {
        return $this->matomoFile;
    }

    /**
     * @param string $matomoUrl
     */
    public function setMatomoFile(string $matomoFile): void
    {
        $this->matomoFile = $matomoFile;
    }

    /**
     * @return string
     */
    public function getMatomoScript(): string
    {
        return $this->matomoScript;
    }

    /**
     * @param string $matomoScript
     */
    public function setMatomoScript(string $matomoScript): void
    {
        $this->matomoScript = $matomoScript;
    }

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @return bool
     */
    public function isGroupByDomain(): bool
    {
        return $this->groupByDomain;
    }

    /**
     * @param bool $groupByDomain
     */
    public function setGroupByDomain(bool $groupByDomain): void
    {
        $this->groupByDomain = $groupByDomain;
    }

    public function isConversionFirstReferrer(): bool
    {
        return $this->conversionFirstReferrer;
    }

    public function setConversionFirstReferrer(bool $conversionFirstReferrer): void
    {
        $this->conversionFirstReferrer = $conversionFirstReferrer;
    }


    /**
     * @return bool
     */
    public function isRequireCookieConsent(): bool
    {
        return $this->requireCookieConsent;
    }

    /**
     * @param bool $requireCookieConsent
     */
    public function setRequireCookieConsent(bool $requireCookieConsent): void
    {
        $this->requireCookieConsent = $requireCookieConsent;
    }

    /**
     * @return bool
     */
    public function iscookieValueAsRegex(): bool
    {
        return $this->cookieValueAsRegex;
    }

    /**
     * @param bool $cookieValueAsRegex
     */
    public function setCookieValueAsRegex(bool $cookieValueAsRegex): void
    {
        $this->cookieValueAsRegex = $cookieValueAsRegex;
    }

    /**
     * @return bool
     */
    public function isDisableCookies(): bool
    {
        return $this->disableCookies;
    }

    /**
     * @param bool $disableCookies
     */
    public function setDisableCookies(bool $disableCookies): void
    {
        $this->disableCookies = $disableCookies;
    }

    /**
     * @return string
     */
    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    /**
     * @param string $cookieName
     */
    public function setCookieName(string $cookieName): void
    {
        $this->cookieName = $cookieName;
    }

    /**
     * @return string
     */
    public function getCookieValue(): string
    {
        return $this->cookieValue;
    }

    /**
     * @param string $cookieValue
     */
    public function setCookieValue(string $cookieValue): void
    {
        $this->cookieValue = $cookieValue;
    }

    /**
     * @return string
     */
    public function getShopHostname(): string
    {
        return $this->shopHostname;
    }

    /**
     * @param string $shopHostname
     */
    public function setShopHostname(string $shopHostname): void
    {
        $this->shopHostname = $shopHostname;
    }

    /**
     * @return string
     */
    public function getUserTrack(): string
    {
        return $this->userTrack;
    }

    /**
     * @param string $userTrack
     */
    public function setUserTrack(string $userTrack): void
    {
        $this->userTrack = $userTrack;
    }

    /**
     * @return string
     */
    public function getHostDomain(): string
    {
        return $this->hostDomain;
    }

    /**
     * @param string $hostDomain
     */
    public function setHostDomain(string $hostDomain): void
    {
        $this->hostDomain = $hostDomain;
    }

    /**
     * @return string
     */
    public function getCookieDomain(): string
    {
        return $this->cookieDomain;
    }

    /**
     * @param string $cookieDomain
     */
    public function setCookieDomain(string $cookieDomain): void
    {
        $this->cookieDomain = $cookieDomain;
    }

    /**
     * @return int
     */
    public function getStartTracking(): int
    {
        return $this->startTracking;
    }

    /**
     * @param int $startTracking
     */
    public function setStartTracking(int $startTracking): void
    {
        $this->startTracking = $startTracking;
    }

    /**
     * @return bool
     */
    public function isUseOwnMatomoScript(): bool
    {
        return $this->useOwnMatomoScript;
    }

    /**
     * @param bool $useOwnMatomoScript
     */
    public function setUseOwnMatomoScript(bool $useOwnMatomoScript): void
    {
        $this->useOwnMatomoScript = $useOwnMatomoScript;
    }

    /**
     * @return string
     */
    public function getUseOwnMatomoScriptCode(): string
    {
        return $this->useOwnMatomoScriptCode;
    }

    /**
     * @param string $useOwnMatomoScriptCode
     */
    public function setUseOwnMatomoScriptCode(string $useOwnMatomoScriptCode): void
    {
        $this->useOwnMatomoScriptCode = $useOwnMatomoScriptCode;
    }

}

