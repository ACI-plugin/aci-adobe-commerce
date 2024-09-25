<?php

namespace Aci\Payment\Gateway\Http\Client;

use Aci\Payment\Helper\Constants as AciConstants;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\HTTP\Header as HttpHeader;
use Psr\Log\LoggerInterface;
use TryzensIgnite\Common\Gateway\Http\Client\PaymentClient as CommonPaymentClient;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Execute API request
 */
class PaymentClient extends CommonPaymentClient
{
    public const ACI_PAYMENT_HEADER_CONTENT_TYPE = 'application/x-www-form-urlencoded';
    /**
     * @var CurlFactory
     */
    protected CurlFactory $curlFactory;
    /**
     * @var Curl
     */
    protected Curl $curlClient;
    /**
     * @var HttpHeader
     */
    protected HttpHeader $httpHeader;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * Payment Client constructor.
     * @param CurlFactory $curlFactory
     * @param Curl $curl
     * @param HttpHeader $httpHeader
     * @param LoggerInterface $logger
     * @param AciGenericPaymentConfig $paymentConfig
     */
    public function __construct(
        curlFactory     $curlFactory,
        Curl            $curl,
        HttpHeader      $httpHeader,
        LoggerInterface $logger,
        AciGenericPaymentConfig $paymentConfig
    ) {
        $this->curlFactory = $curlFactory;
        $this->curlClient = $curl;
        $this->httpHeader = $httpHeader;
        $this->logger = $logger;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Places request to gateway. Returns result as array
     *
     * @param TransferInterface $transferObject
     * @return array|mixed
     * @throws LocalizedException
     * @throws Exception
     */
    public function placeRequest(TransferInterface $transferObject): mixed
    {
        $api_key = $this->paymentConfig->getApiKey();
        $headers = $transferObject->getHeaders();
        $headers['Content-Type'] = self::ACI_PAYMENT_HEADER_CONTENT_TYPE;
        $headers['Authorization'] = 'Bearer ' . $api_key;
        $params = $transferObject->getBody();
        $method = $transferObject->getMethod();
        $response = match ($method) {
            AciConstants::API_METHOD_GET => $this->getRequest($transferObject->getUri(), (array)$params, $headers),
            default => $this->postRequest($transferObject->getUri(), $params, $headers),
        };
        $this->logger->info('API Response::' . $response);
        if ($response) {
            return json_decode($response, true);
        }
        return [];
    }

    /**
     * Execute Curl POST request
     *
     * @param string $url
     * @param string|array<mixed> $params
     * @param array<mixed> $headers
     * @return string
     * @throws LocalizedException
     */
    public function postRequest(
        string       $url,
        array|string $params,
        array        $headers
    ): string {
        try {
            $this->curlClient->setHeaders($headers);
            $this->logger->info('Send Request to :: ' . $url);
            $this->logger->info('Request params :: ' . json_encode($params));
            $this->curlClient->post($url, $params);
            return $this->curlClient->getBody();
        } catch (Exception $exception) {
            $this->logger->error('API Client: POST request error:: ' . $exception->getMessage());
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * Execute Curl GET request
     *
     * @param string $url
     * @param array<mixed> $params
     * @param array<mixed> $headers
     * @return string
     * @throws LocalizedException
     */
    public function getRequest(
        string       $url,
        array        $params,
        array        $headers
    ): string {
        $url .= '?entityId='.$params[AciConstants::KEY_ACI_PAYMENT_ENTITY_ID];
        try {
            $this->curlClient->setHeaders($headers);
            $this->logger->info('Send Request to :: ' . $url);
            $this->curlClient->get($url);
            return $this->curlClient->getBody();
        } catch (Exception $exception) {
            $this->logger->error('API Client: GET request error:: ' . $exception->getMessage());
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
