<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Helper\Constants as AciConstants;
use Magento\Payment\Gateway\Http\TransferBuilder;
use TryzensIgnite\Base\Gateway\Http\TransferFactory\TransferFactory as IgniteTransferFactory;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * TransferFactory for Get Status transaction commands
 */
class GetStatusTransferFactory extends IgniteTransferFactory
{
    /**
     * @var AciGenericPaymentConfig
     */
    protected AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @param TransferBuilder $transferBuilder
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     * @param string $method
     * @param string $endPoint
     */
    public function __construct(
        TransferBuilder     $transferBuilder,
        AciGenericPaymentConfig $aciGenericPaymentConfig,
        string              $method = AciConstants::API_METHOD_GET,
        string              $endPoint = AciConstants::GET_STATUS_TRANSACTION_TYPE,
    ) {
        parent::__construct(
            $transferBuilder,
            $aciGenericPaymentConfig,
            $method,
            $endPoint
        );
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
    }

    /**
     * Build default URI
     *
     * @param string $endPoint
     * @return string
     */
    public function buildDefaultUri(string $endPoint): string
    {
        return (string)$this->config->getApiEndPoint();
    }

    /**
     * Get transaction id from the request
     *
     * @param array<mixed> $request
     * @return string
     */
    public function getTransactionId(array $request): string
    {
        return $request[AciConstants::KEY_CHECKOUT_ID] ?? '';
    }

    /**
     * Build GetTransaction URI
     *
     * @param string $defaultUri
     * @param string $transactionId
     * @return string
     */
    public function buildGetTransactionUri(string $defaultUri, string $transactionId): string
    {
        $endPoint = sprintf(
            AciConstants::END_POINT_GET_STATUS,
            $transactionId
        );
        $entityId = $this->aciGenericPaymentConfig->getEntityId();
        return $defaultUri.$endPoint . '?entityId=' . $entityId;
    }

    /**
     * Get headers for API call
     *
     * @param array<mixed> $credentials
     * @return array<mixed>
     */
    public function getHeaders(array $credentials): array
    {
        return [
            'Content-Type' => AciConstants::ACI_PAYMENT_HEADER_CONTENT_TYPE,
            'Authorization' => 'Bearer ' . $credentials['apiKey']
        ];
    }

    /**
     * Get Transaction Type
     *
     * @return string
     */
    public function getEndPoint(): string
    {
        return AciConstants::GET_STATUS_TRANSACTION_TYPE;
    }
}
