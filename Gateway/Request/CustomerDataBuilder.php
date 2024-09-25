<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Helper\Data;
use Aci\Payment\Helper\Constants;

/**
 * Class CustomerDataBuilder
 * Builds customer data
 */
class CustomerDataBuilder implements BuilderInterface
{
    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var Data
     */
    private Data $data;

    /**
     * @param Utilities $utilities
     * @param Data $data
     */
    public function __construct(
        Utilities $utilities,
        Data $data
    ) {
        $this->utilities = $utilities;
        $this->data = $data;
    }

    /**
     * Builds customer data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();
        $billingAddress = $order->getBillingAddress();
        $prefix = Constants::CUSTOMER_PREFIX. '.';
        $customerArray = [
            $prefix.Constants::KEY_FIRST_NAME => $billingAddress?->getFirstname(),
            $prefix.Constants::KEY_LAST_NAME => $billingAddress?->getLastname(),
            $prefix.Constants::KEY_CUSTOMER_PHONE => $billingAddress?->getTelephone(),
            $prefix.Constants::KEY_CUSTOMER_EMAIL => $billingAddress?->getEmail(),
            $prefix.Constants::KEY_CUSTOMER_IP => $this->data->getCustomerIpAddress(),
        ];
        if ($order->getCustomerId()) {
            $customerArray[$prefix.Constants::KEY_CUSTOMER_ID] = $order->getCustomerId();
        }
        return $this->utilities->removeNullValues($customerArray);
    }
}
