<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Routing;

use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectCollection;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectEntity;
use DreiscSeoPro\Core\Content\DreiscSeoRedirect\DreiscSeoRedirectRepository;
use DreiscSeoPro\Test\Core\Routing\RedirectFinderTest;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\HttpFoundation\Request;

/** @see RedirectFinderTest */
class RedirectFinder
{
    /** @deprecated Muss verschoben werden, da es nicht nur für den Finder relevant ist */
    public const QUERY_PARAMS = 'query-params';

    public function __construct(
        private readonly DreiscSeoRedirectRepository $dreiscSeoRedirectRepository
    ) {}

    public function findBySourceUrl(string $relativeUrl, string $salesChannelDomainId): ?DreiscSeoRedirectEntity
    {
        $dreiscSeoRedirectCollection = $this->dreiscSeoRedirectRepository->getSourceTypeUrlByDomainIdAndSourcePath(
            $salesChannelDomainId,
            $relativeUrl
        );

        if (null !== $dreiscSeoRedirectCollection->first()) {
            return $dreiscSeoRedirectCollection->first();
        }

        /** Determine URL without GET parameters */
        $requestedPathInfo = $this->parseUrl($relativeUrl);

        /** Only continue searching if the URL has GET parameters. Otherwise abort */
        if (empty($requestedPathInfo['query'])) {
            return null;
        }

        if(empty($requestedPathInfo['path'])) {
            $requestedPathInfo['path'] = '';
        }

        /** Fetch all redirects beginning with the base url */
        $dreiscSeoRedirectCollection = $this->dreiscSeoRedirectRepository->getSourceTypeUrlByDomainIdAndSourcePath(
            $salesChannelDomainId,
            $requestedPathInfo['path'],
            true
        );

        if (0 === $dreiscSeoRedirectCollection->count()) {
            return null;
        }

        if (
            1 === $dreiscSeoRedirectCollection->count() &&
            strtolower($dreiscSeoRedirectCollection->first()->getSourcePath()) === strtolower($requestedPathInfo['path'])
        ) {
            /** If the redirect is an exact match, return it */
            /** Example: from-url?foo=bar&baz=qux matches to from-url */
            $dreiscSeoRedirect = $dreiscSeoRedirectCollection->first();

            /** Add additional parameters to an entity extension */
            $this->addQueryParamsToRedirect($dreiscSeoRedirect, $requestedPathInfo);

            return $dreiscSeoRedirectCollection->first();
        } elseif ($dreiscSeoRedirectCollection->count() > 1 || empty($requestedPathInfo['path'])) {
            $possibleRedirects = $this->getPossibleRedirects($dreiscSeoRedirectCollection, $requestedPathInfo);
            if (empty($possibleRedirects)) {
                return null;
            } elseif (1 === count($possibleRedirects)) {
                $this->addQueryParamsToRedirect($possibleRedirects[0], $requestedPathInfo);
                return $possibleRedirects[0];
            }

            $possibleRedirect = $this->getPriorityRedirect($possibleRedirects, $requestedPathInfo);
            $this->addQueryParamsToRedirect($possibleRedirect, $requestedPathInfo);

            return $possibleRedirect;
        }

        return null;
    }

    private function addQueryParamsToRedirect(DreiscSeoRedirectEntity $dreiscSeoRedirect, array $requestedPathInfo): void
    {
        $dreiscSeoRedirect->addExtension(
            self::QUERY_PARAMS,
            new ArrayStruct($requestedPathInfo['query'])
        );
    }

    /**
     * @param DreiscSeoRedirectCollection|null $dreiscSeoRedirectCollection
     * @param $requestedPathInfo
     * @return DreiscSeoRedirectEntity[]
     */
    private function getPossibleRedirects(?DreiscSeoRedirectCollection $dreiscSeoRedirectCollection, $requestedPathInfo): array
    {
        $possibleRedirects = [];
        foreach($dreiscSeoRedirectCollection->getElements() as $dreiscSeoRedirect) {
            $dreiscSeoRedirectSourcePathInfo = parse_url($dreiscSeoRedirect->getSourcePath());
            if(empty($dreiscSeoRedirectSourcePathInfo['query'])) {
                /** Is possible, because it is the url without params */
                $possibleRedirects[] = $dreiscSeoRedirect;
                continue;
            }

            parse_str($dreiscSeoRedirectSourcePathInfo['query'], $dreiscSeoRedirectSourcePathInfo['query']);
            foreach($dreiscSeoRedirectSourcePathInfo['query'] as $variable => $value) {
                if(!isset($requestedPathInfo['query'][$variable])) {
                    continue 2;
                }

                if(!empty($value) && $requestedPathInfo['query'][$variable] !== $value) {
                    continue 2;
                }
            }

            $possibleRedirects[] = $dreiscSeoRedirect;
        }
        
        return $possibleRedirects;
    }

    /**
     * @param DreiscSeoRedirectEntity[] $possibleRedirects
     * @param $requestedPathInfo
     * @return DreiscSeoRedirectEntity
     */
    private function getPriorityRedirect(array $possibleRedirects, $requestedPathInfo)
    {
        /** Sort by number of matching parameters */
        usort($possibleRedirects, function($a, $b) use ($requestedPathInfo) {
            $aSourcePathInfo = $this->parseUrl($a->getSourcePath());
            $bSourcePathInfo = $this->parseUrl($b->getSourcePath());

            $aMatches = 0;
            $bMatches = 0;

            foreach($aSourcePathInfo['query'] as $variable => $value) {
                if(isset($requestedPathInfo['query'][$variable])) {
                    $aMatches++;
                }
            }

            foreach($bSourcePathInfo['query'] as $variable => $value) {
                if(isset($requestedPathInfo['query'][$variable])) {
                    $bMatches++;
                }
            }

            return $bMatches <=> $aMatches;
        });

        return $possibleRedirects[0];
    }

    private function parseUrl(string $url): array
    {
        $pathInfo = parse_url($url);

        if(empty($pathInfo['query'])) {
            $pathInfo['query'] = [];
            return $pathInfo;
        }

        parse_str($pathInfo['query'], $pathInfo['query']);
        return $pathInfo;
    }
}
