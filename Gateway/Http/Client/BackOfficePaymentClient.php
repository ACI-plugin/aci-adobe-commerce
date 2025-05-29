<?php

namespace Aci\Payment\Gateway\Http\Client;

use Aci\Payment\Helper\Constants as AciConstants;
use Magento\Payment\Gateway\Http\TransferInterface;
use TryzensIgnite\Base\Gateway\Http\Client\PaymentClient as IgnitePaymentClient;

/**
 * Execute API request
 */
class BackOfficePaymentClient extends IgnitePaymentClient
{
    /**
     * Get the request body
     *
     * @param TransferInterface $transferObject
     * @return string|array<mixed>
     */
    protected function getRequestParams(TransferInterface $transferObject): string|array
    {
        $request = $transferObject->getBody();
        if (isset($request[AciConstants::KEY_CHECKOUT_ID]) && $request[AciConstants::KEY_CHECKOUT_ID]) {
            unset($request[AciConstants::KEY_CHECKOUT_ID]);
        }

        return $request;
    }
}
