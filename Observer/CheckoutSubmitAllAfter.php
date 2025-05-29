<?php

namespace Aci\Payment\Observer;

use TryzensIgnite\Onsite\Model\Ui\CcConfigProvider;
use TryzensIgnite\Onsite\Model\Ui\ApplePayConfigProvider;
use TryzensIgnite\Onsite\Model\Ui\GooglePayConfigProvider;
use Aci\Payment\Model\Ui\AciApmConfigProvider;
use TryzensIgnite\Onsite\Observer\CheckoutSubmitAllAfter as IgniteCheckoutSubmitAllAfter;

/**
 * Update order status after place order
 */
class CheckoutSubmitAllAfter extends IgniteCheckoutSubmitAllAfter
{
    /**
     * @var array<mixed>
     */
    protected array $onsitePaymentMethods = [
        CcConfigProvider::CODE,
        ApplePayConfigProvider::CODE,
        GooglePayConfigProvider::CODE,
        CcConfigProvider::ONSITE_CC_VAULT_CODE,
        AciApmConfigProvider::CODE
    ];
}
