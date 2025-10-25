<?php

/**
 * Metropol
 * @author    Sparkworld Enterprises <sparkworldke@gmail.com>
 * @copyright Copyright (c) Sparkworld Enterprises
 */

namespace SparkMetropolApi;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Metropol
{
    /**
     * @var mixed
     */
    private $publicApiKey;
    /**
     * @var mixed
     */
    private $privateApiKey;

    /**
     * @var string
     */
    private $baseEndpoint = "https://api.metropol.co.ke";

    /**
     * Always clarify the port with MCRB before making any connection
     * @var mixed
     */
    private $port = 443;

    /**
     * @var Client
     */
    private $http;

    /**
     * The Metropol API version
     * @var string
     */
    private $version = 'v2';

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Metropol constructor.
     * @param $publicApiKey
     * @param $privateApiKey
     */
    public function __construct($publicApiKey, $privateApiKey)
    {
        $this->publicApiKey = $publicApiKey;
        $this->privateApiKey = $privateApiKey;

        $this->http = new Client([
            'timeout'         => 60,
            'allow_redirects' => true,
            'http_errors'     => true, //let users handle errors
            'verify'          => false,
        ]);
    }

    /**
     * @param $version
     * @return Metropol
     */
    public function withVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getVersion()
    {
        if (!is_null($this->version)) {
            return '/' . $this->version;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseEndpoint . ":" . $this->port;
    }

    /**
     * @param $publicApiKey
     * @return Metropol
     */
    public function withPublicApiKey($publicApiKey)
    {
        $this->publicApiKey = $publicApiKey;
        return $this;
    }

    /**
     * @param $privateApiKey
     * @return Metropol
     */
    public function withPrivateApiKey($privateApiKey)
    {
        $this->privateApiKey = $privateApiKey;
        return $this;
    }

    /**
     * @param $baseEndpoint
     * @return Metropol
     */
    public function withBaseEndpoint($baseEndpoint)
    {
        $this->baseEndpoint = $baseEndpoint;
        return $this;
    }

    /**
     * @param $port
     * @return Metropol
     */
    public function withPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param Client $http
     * @return Metropol
     */
    public function withHttp(Client $http)
    {
        $this->http = $http;
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return Metropol
     */
    public function withLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param $payload
     * @return array
     */
    private function setHeaders($payload)
    {
        //calculate the timestamp as required e.g 2014 07 08 17 58 39 987843
        //Format: Year, Month, Day, Hour, Minute, Second, Milliseconds
        $now = Carbon::now('UTC');

        $apiTimestamp = $now->format('Y-m-d-H-i-s-u');
        $apiTimestamp = str_replace('-', '', $apiTimestamp);

        //calculate the rest api hash as required
        $apiHash = $this->calculateHash($payload, $apiTimestamp);

        $headers = [
            "X-METROPOL-REST-API-KEY:" . $this->publicApiKey,
            "X-METROPOL-REST-API-HASH:" . $apiHash,
            "X-METROPOL-REST-API-TIMESTAMP:" . $apiTimestamp,
            "Content-Type:application/json"
        ];

        $this->log("Metropol API Headers:", $headers);

        return array_values($headers);
    }

    /**
     * @param $payload
     * @param $apiTimestamp
     * @return string
     */
    private function calculateHash($payload, $apiTimestamp)
    {
        $string = $this->privateApiKey . trim(json_encode($payload)) . $this->publicApiKey . $apiTimestamp;

        return hash('sha256', $string);
    }

    /**
     * @param $endpoint
     * @param $payload
     * @return array
     */
    public function httpPost($endpoint, $payload)
    {
        $url = $this->getBaseUrl() . $this->getVersion() . $endpoint;

        $this->log("Metropol API URL:" . $url);
        $this->log("Metropol API Payload:", $payload);

        $response = $this->http->request('POST', $url, [
            'json'    => $payload,
            'headers' => $this->setHeaders($payload),
        ]);

        $contents = $response->getBody()->getContents();

        $this->log("Metropol API Response:" . $contents);

        return json_decode($contents);
    }

    /**
     * @param $message
     * @param array $context
     */
    public function log($message, $context = [])
    {
        if ($this->logger) {
            $this->logger->log("info", $message, $context);
        }
    }

    /**
     * @param $id_number
     * @return array
     */
    public function identityVerification($id_number)
    {
        $endpoint = '/identity/verify';

        $payload = [
            "report_type"     => 1,
            "identity_number" => (string) $id_number,
            "identity_type"   => "001",
        ];

        return $this->httpPost($endpoint, $payload);
    }

    /**
     * @param $id_number
     * @param $loan_amount
     * @return array
     */
    public function deliquencyStatus($id_number, $loan_amount)
    {
        $endpoint = '/deliquency/status';

        $payload = [
            "report_type"     => 2,
            "identity_number" => (string) $id_number,
            "identity_type"   => "001",
            "loan_amount"     => $loan_amount,
        ];

        return $this->httpPost($endpoint, $payload);
    }

    /**
     * @param $id_number
     * @param $loan_amount
     * @return array
     */
    public function creditInfo($id_number, $loan_amount)
    {
        $endpoint = '/report/credit_info';

        $payload = [
            "report_type"     => 8,
            "identity_number" => (string) $id_number,
            "identity_type"   => "001",
            "loan_amount"     => $loan_amount,
            "report_reason"   => 1,
        ];

        return $this->httpPost($endpoint, $payload);
    }

    /**
     * @param $id_number
     * @return array
     */
    public function consumerScore($id_number)
    {
        $endpoint = '/score/consumer';

        $payload = [
            "report_type"     => 3,
            "identity_number" => (string) $id_number,
            "identity_type"   => "001",
        ];

        return $this->httpPost($endpoint, $payload);
    }

}
