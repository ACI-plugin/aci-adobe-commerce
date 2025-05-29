<?php

namespace Aci\Payment\Model\Http;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use TryzensIgnite\Base\Logger\Logger;

/**
 * Executes API request
 */
class Client extends Curl
{
    /**
     * @var Curl
     */
    private Curl $curlClient;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var HeaderInterface
     */
    private HeaderInterface $headers;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Curl $curl
     * @param Serializer $serializer
     * @param HeaderInterface $headers
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        Serializer $serializer,
        HeaderInterface $headers,
        Logger $logger
    ) {
        parent::__construct();
        $this->curlClient   = $curl;
        $this->serializer   = $serializer;
        $this->headers = $headers;
        $this->logger = $logger;
    }

    /**
     * Process Request
     *
     * @param string $requestType
     * @param string $apiUrl
     * @param array<mixed> $uriParams
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function processRequest(
        string $requestType,
        string $apiUrl,
        array $uriParams = [],
    ): array {
        try {
            $uriParams = (string)$this->serializer->serialize($uriParams);
            /** @phpstan-ignore-next-line */
            $this->curlClient->setHeaders($this->headers->getHeaders());

            $this->logger->info(__('-- Start API Request From Curl Client Model --'));
            $this->logger->info(__('Request Type: ' . $requestType));
            $this->logger->info(__('API Url: ' . $apiUrl));
            $this->logger->info(__('Uri Params: ' . $uriParams));
            $this->curlClient->makeRequest($requestType, $apiUrl, $uriParams);

            $responseBody = $this->curlClient->getBody();
            $responseBody = $responseBody ? $this->serializer->unserialize($responseBody) : [];
            $this->logger->info(__('API Response: '. json_encode($responseBody)));
            $this->logger->info(__('-- End API Request From Curl Client Model --'));

            return [
                'response'  =>  $responseBody,
                'status'    =>  $this->curlClient->_responseStatus
            ];
        } catch (Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
