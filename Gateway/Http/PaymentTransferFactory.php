<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Helper\Constants as AciConstants;
use TryzensIgnite\Base\Gateway\Http\TransferFactory\TransferFactory as IgniteTransferFactory;

/**
 * TransferFactory for storefront transaction commands
 */
class PaymentTransferFactory extends IgniteTransferFactory
{
    /**
     * Get transaction id from the request
     *
     * @param array<mixed> $request
     * @return string
     */
    public function getTransactionId(array $request): string
    {
        return $request[AciConstants::KEY_ACI_PAYMENT_ENTITY_ID] ?? '';
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
}
