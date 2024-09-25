<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Data;
use Aci\Payment\Helper\Constants;

/**
 * Class PaymentDataBuilder
 * Builds payment type
 */
class CcPaymentTypeDataBuilder implements BuilderInterface
{
    /**
     * @var Data
     */
    protected Data $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
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
            Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE => $this->dataHelper->getCcPaymentType(),
        ];
    }
}
