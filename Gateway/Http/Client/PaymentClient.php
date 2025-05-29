<?php

namespace Aci\Payment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use TryzensIgnite\Base\Gateway\Http\Client\PaymentClient as IgnitePaymentClient;

/**
 * Execute API request
 */
class PaymentClient extends IgnitePaymentClient
{
    /**
     * Get the request body
     *
     * @param TransferInterface $transferObject
     * @return string|array<mixed>
     */
    protected function getRequestParams(TransferInterface $transferObject): string|array
    {
        return $transferObject->getBody();
    }
}
