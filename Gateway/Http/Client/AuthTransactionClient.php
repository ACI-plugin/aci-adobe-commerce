<?php

namespace Aci\Payment\Gateway\Http\Client;

use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Execute API request for AUTH service
 */
class AuthTransactionClient implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as array
     *
     * @param TransferInterface $transferObject
     * @return array<mixed>
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $headers = $transferObject->getHeaders();

        if (isset($headers[Constants::GET_TRANSACTION_ID])) {
            return [Constants::GET_TRANSACTION_ID => $headers[Constants::GET_TRANSACTION_ID]];
        }

        return [];
    }
}
