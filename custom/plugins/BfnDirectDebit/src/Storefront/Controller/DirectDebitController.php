<?php declare(strict_types=1);

namespace Bfn\DirectDebit\Storefront\Controller;

use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class DirectDebitController extends StorefrontController
{
    #[Route(path: '/directDebit/validateIban/iban', name: 'frontend.bfn.directdebit.validate.iban', defaults:['XmlHttpRequest' => true], options: ["seo"=>false], methods: ['POST', 'GET'])]
    public function validateIban(Request $request): Response
    {
        $iban = $request->request->get('getIbanNumber');
        // Remove any spaces and convert to uppercase
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Check if the IBAN format is valid
        if (!preg_match("/^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/", $iban)) {
            return new JsonApiResponse( false);
        }

        // Extract the country code and check its validity
        $countryCode = substr($iban, 0, 2);
        if (!$this->isValidCountryCode($countryCode)) {
            return new JsonApiResponse( false);
        }

        // Move the four initial characters to the end of the IBAN
        $rearrangedIBAN = substr($iban, 4) . substr($iban, 0, 4);

        // Convert letters to digits (A = 10, B = 11, etc.)
        $numericIBAN = '';
        for ($i = 0; $i < strlen($rearrangedIBAN); $i++) {
            $charCode = ord($rearrangedIBAN[$i]);
            if ($charCode >= 65 && $charCode <= 90) {
                $numericIBAN .= strval($charCode - 55);
            } else {
                $numericIBAN .= $rearrangedIBAN[$i];
            }
        }

        // Perform modulo-97 check
        $remainder = 0;
        for ($j = 0; $j < strlen($numericIBAN); $j++) {
            $remainder = ($remainder * 10 + intval($numericIBAN[$j])) % 97;
        }

        return new JsonApiResponse( $remainder === 1);
    }

    #[Route(path: '/directDebit/validateSwift/swiftCode', name: 'frontend.bfn.directdebit.validate.swift', defaults:['XmlHttpRequest' => true], options: ["seo"=>false], methods: ['POST', 'GET'])]
    public function validateSwift(Request $request): Response
    {   
        $swiftCode = $request->request->get('getSwiftNumber');
        $swiftCode = strtoupper($swiftCode);
        $swiftCode = str_replace(' ', '', $swiftCode);
        $pattern = "/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/";
        return new JsonApiResponse( preg_match($pattern, $swiftCode) === 1);
    }

    /**
     * Helper function to check if the country code is valid
     *
     * @param $countryCode
     * @return bool
     */
    private function isValidCountryCode($countryCode): bool
    {   
        // List of valid country codes and their corresponding IBAN length
        $countryList = array(
            'AL' => 28, 'AD' => 24, 'AT' => 20, 'AZ' => 28, 'BE' => 16, 'BA' => 20, 'BG' => 22,
            'HR' => 21, 'CY' => 28, 'CZ' => 24, 'DK' => 18, 'EE' => 20, 'FO' => 18, 'FI' => 18,
            'FR' => 27, 'GE' => 22, 'DE' => 22, 'GI' => 23, 'GR' => 27, 'GL' => 18, 'HU' => 28,
            'IS' => 26, 'IE' => 22, 'IL' => 23, 'IT' => 27, 'KZ' => 20, 'KW' => 30, 'LV' => 21,
            'LB' => 28, 'LI' => 21, 'LT' => 20, 'LU' => 20, 'MK' => 19, 'MT' => 31, 'MR' => 27,
            'MU' => 30, 'MC' => 27, 'MD' => 24, 'ME' => 22, 'NL' => 18, 'NO' => 15, 'PK' => 24,
            'PS' => 29, 'PL' => 28, 'PT' => 25, 'RO' => 24, 'SM' => 27, 'SA' => 24, 'RS' => 22,
            'SK' => 24, 'SI' => 19, 'ES' => 24, 'SE' => 24, 'CH' => 21, 'TN' => 24, 'TR' => 26,
            'AE' => 23, 'GB' => 22, 'VG' => 24
        );

        return array_key_exists($countryCode, $countryList);
    }
}