<?php declare(strict_types = 1);

namespace Cbax\ModulLexicon\Components\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;

use Psr\Http\Message\ResponseInterface;

class GuzzleClient
{
    const LOG_TYPE_ERROR = 'Error';
    const METHOD_POST = 'POST';

    public static function call($baseUrl, $query, $settings, $headers, $method, $formParams, $options = null) : ResponseInterface|string|array
    {
        $clientConfigs = [];
        $clientConfigs['base_uri'] = $baseUrl;
        $clientConfigs['body'] = $query;
        $clientConfigs['headers'] = $headers;
        $clientConfigs['timeout'] = 60;

        if ($formParams !== null) {
            $clientConfigs['form_params'] = $formParams;
        }

        if (!empty($settings['proxy'])) {
            $clientConfigs['proxy'] = $settings['proxy'];
        }

        try
        {
            if (!empty($options)) {
                $client = new Client();
                $response = $client->request($method, $baseUrl, array_merge(['headers' => $headers], $options));
            } else {
                $client = new Client($clientConfigs);
                $response = $client->request($method);
            }

            if (empty($response)) return $response;

            $responseStr = $response->getBody()->getContents();

            if (!empty($options) && !empty($options['sink'])) {
                return $response;
            }

            if (!empty($responseStr)) {
                return $responseStr;
            } else {
                return $response;
            }

        } catch (RequestException $e) {
            $request = [
                $settings,
                $clientConfigs
            ];

            $message = $e->getMessage();
            $detail = $message;

            if (!empty($e->getResponse())) {
                $detail = Utils::copyToString($e->getResponse()->getBody(), 255);
            }

            try {
                $traceDetails = [
                    'Message' => $e->getMessage(),
                    'Previous' => $e->getPrevious(),
                    'Code' => $e->getCode(),
                    'File' => $e->getFile(),
                    'Line' => $e->getLine(),
                    'Trace' => $e->getTrace(),
                    'TraceString' => $e->getTraceAsString()
                ];
            } catch (\Exception $error) {
                $traceDetails = [];
            }

            $exceptionArray = ['Message' => $message, 'Detail' => $detail, 'traceDetails' => $traceDetails];

            return [
                'Typ'       => self::LOG_TYPE_ERROR,
                'Request'   => $request,
                'Query'     => $query,
                'Response'  => $exceptionArray,
                'success'   => false

            ];
        }
    }
}
