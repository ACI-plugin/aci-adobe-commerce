<?php

namespace Aci\Payment\Model\Http;

use Magento\Framework\Exception\LocalizedException;

/**
 * Executes API request
 */
class Request
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * Send API request
     *
     * @param string $requestType
     * @param string $apiUri
     * @param array<mixed> $apiParams
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function sendRequest(
        string $requestType,
        string $apiUri,
        array $apiParams = []
    ): array {
        $response = $this->client->processRequest(
            $requestType,
            $apiUri,
            $apiParams
        );

        return $response['response'];
    }
}
