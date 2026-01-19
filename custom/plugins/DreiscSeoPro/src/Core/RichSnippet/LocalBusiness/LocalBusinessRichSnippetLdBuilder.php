<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RichSnippet\LocalBusiness;

use DreiscSeoPro\Core\Content\Country\CountryRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

class LocalBusinessRichSnippetLdBuilder implements LocalBusinessRichSnippetLdBuilderInterface
{
    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @param CountryRepository $countryRepository
     */
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function build(LocalBusinessRichSnippetLdBuilderStruct $localBusinessRichSnippetLdBuilderStruct): array
    {
        $localBusinessSettings = $localBusinessRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getLocalBusiness();

        /** Abort, if the transfer of the logo data is inactive */
        if (true !== $localBusinessSettings->getGeneral()->isActive()) {
            return [];
        }

        /** Base fields */
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness'
        ];

        /** Set name, if exists */
        if(!empty($localBusinessSettings->getGeneral()->getName())) {
            $ld['name'] = $localBusinessSettings->getGeneral()->getName();
        }

        /** Set url, if exists */
        if(!empty($localBusinessSettings->getGeneral()->getUrl())) {
            $ld['url'] = $localBusinessSettings->getGeneral()->getUrl();
        }

        /** Set telephone, if exists */
        if(!empty($localBusinessSettings->getGeneral()->getTelephone())) {
            $ld['telephone'] = $localBusinessSettings->getGeneral()->getTelephone();
        }

        /** Set address, if exists */
        $address = $this->fetchAddress($localBusinessRichSnippetLdBuilderStruct);
        if(!empty($address)) {
            $ld['address'] = $address;
        }

        /** Set openingHoursSpecification, if exists */
        $openingHoursSpecification = $this->fetchOpeningHoursSpecification($localBusinessRichSnippetLdBuilderStruct);
        if(!empty($openingHoursSpecification)) {
            $ld['openingHoursSpecification'] = $openingHoursSpecification;
        }

        /** Add the static price range */
        $ld['priceRange'] = '€€€';

        return $ld;
    }

    /**
     * @return array|string[]
     * @throws InconsistentCriteriaIdsException
     */
    private function fetchAddress(LocalBusinessRichSnippetLdBuilderStruct $localBusinessRichSnippetLdBuilderStruct): array
    {
        $localBusinessSettings = $localBusinessRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getLocalBusiness();

        /** Base fields */
        $streetAddress = $localBusinessSettings->getAddress()->getStreetAddress();
        $addressLocality = $localBusinessSettings->getAddress()->getAddressLocality();
        $postalCode = $localBusinessSettings->getAddress()->getPostalCode();
        $addressCountry = $localBusinessSettings->getAddress()->getAddressCountry();

        /** Abort, if no field is set */
        if (empty($streetAddress) && empty($addressLocality) && empty($postalCode) && empty($addressCountry)) {
            return [];
        }

        /** Base ld */
        $ld = [
            '@type' => 'PostalAddress'
        ];

        /** Set streetAddress, if exists */
        if(!empty($streetAddress)) {
            $ld['streetAddress'] = $streetAddress;
        }

        /** Set addressLocality, if exists */
        if(!empty($addressLocality)) {
            $ld['addressLocality'] = $addressLocality;
        }

        /** Set postalCode, if exists */
        if(!empty($postalCode)) {
            $ld['postalCode'] = $postalCode;
        }

        /** Set addressCountry, if exists */
        if(!empty($addressCountry)) {
            /** Fetch the country entity for the given country id */
            $countryEntity = $this->countryRepository->get($addressCountry);

            if (null !== $countryEntity) {
                $ld['addressCountry'] = $countryEntity->getIso();
            }
        }

        return $ld;
    }

    /**
     * @return array|string[]
     * @throws InconsistentCriteriaIdsException
     */
    private function fetchOpeningHoursSpecification(LocalBusinessRichSnippetLdBuilderStruct $localBusinessRichSnippetLdBuilderStruct): array
    {
        $localBusinessSettings = $localBusinessRichSnippetLdBuilderStruct->getCustomSetting()->getRichSnippets()->getLocalBusiness();
        $openingHoursSpecification = $localBusinessSettings->getOpeningHoursSpecification();

        /** Abort, if no specifications are set */
        if (null === $openingHoursSpecification) {
            return [];
        }

        /** Base ld */
        $ld = [];

        /** Iterate specifications and group by open and close times */
        $groupedSpecifications = [];
        foreach($openingHoursSpecification->toArray() as $dayOfWeek => $specification) {
            /** Continue, if day is not active */
            if (true !== $specification['active']) {
                continue;
            }

            /** Base fields */
            $dayOfWeek = ucfirst((string) $dayOfWeek);
            $opens = $specification['opens'];
            $closes = $specification['closes'];

            /** Continue, if specification is not complete */
            if (empty($dayOfWeek) || empty($opens) || empty($closes)) {
                continue;
            }

            /** Create opens / closes time hash */
            $timeHash = md5($opens . $closes);

            /** Create entry if not exists */
            if (!isset($groupedSpecifications[$timeHash])) {
                $groupedSpecifications[$timeHash] = [
                    'daysOfWeek' => [],
                    'opens' => $opens,
                    'closes' => $closes
                ];
            }

            /** Add the day to the daysOfWeek array */
            $groupedSpecifications[$timeHash]['daysOfWeek'][] = $dayOfWeek;
        }

        /** Abort, if the grouped specifications array is empty */
        if(empty($groupedSpecifications)) {
            return [];
        }

        /** Iterate the grouped specifications */
        foreach($groupedSpecifications as $groupedSpecification) {
            /** Add specification */
            $ld[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $groupedSpecification['daysOfWeek'],
                'opens' => $groupedSpecification['opens'],
                'closes' => $groupedSpecification['closes']
            ];
        }

        return $ld;
    }
}
