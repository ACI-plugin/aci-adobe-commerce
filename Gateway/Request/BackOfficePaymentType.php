<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;

/**
 * Payment Type Request builder for backoffice operation
 */
class BackOfficePaymentType implements BuilderInterface
{
    /**
     * @var string
     */
    protected string $paymentType;

    /**
     * @param string $paymentType
     */
    public function __construct(
        string $paymentType
    ) {
        $this->paymentType = $paymentType;
    }

    /**
     * Builds payment type for card type payment
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        return [
            Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE => $this->paymentType,
        ];
    }
}
