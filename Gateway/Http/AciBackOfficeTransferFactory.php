<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Helper\Constants as AciConstants;
use TryzensIgnite\Base\Gateway\Http\TransferFactory\TransferFactory as IgniteTransferFactory;

/**
 * TransferFactory for Back Office Operation
 */
class AciBackOfficeTransferFactory extends IgniteTransferFactory
{
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
     * Build default URI
     *
     * @param string $endPoint
     * @return string
     */
    public function buildDefaultUri(string $endPoint): string
    {
        return $this->config->getApiEndPoint() . $endPoint;
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
        return AciConstants::END_POINT_BACKOFFICE_OPERATION;
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
        return $defaultUri . $transactionId;
    }
}
