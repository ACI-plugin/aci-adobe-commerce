<?php

declare(strict_types=1);

namespace Aci\Payment\Model\Subscription;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Configuration class
 */
class Config extends AciGenericPaymentConfig
{
    public const KEY_ACTIVE = 'payment/aci_subscription/active';
    public const KEY_RECURRING_FREQUENCY = 'payment/aci_subscription/subscription_frequency';
    public const KEY_RECURRING_REFUND = 'payment/aci_subscription/enable_refund';
    public const KEY_SALES_SUPPORT_EMAIL = 'trans_email/ident_sales/email';
    public const KEY_SALES_SUPPORT_NAME = 'trans_email/ident_sales/name';
}
