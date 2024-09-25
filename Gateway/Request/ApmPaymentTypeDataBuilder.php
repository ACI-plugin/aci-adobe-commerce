<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Data;
use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Aci\Payment\Logger\AciLogger;

/**
 * Class PaymentDataBuilder
 * Builds payment type
 */
class ApmPaymentTypeDataBuilder implements BuilderInterface
{
    /**
     * @var Data
     */
    protected Data $dataHelper;

    /**
     * @var AciLogger
     */
    private AciLogger $logger;

    /**
     * @param Data $dataHelper
     * @param AciLogger $logger
     */
    public function __construct(
        Data $dataHelper,
        AciLogger $logger,
    ) {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * Builds payment type for card type payment
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $additionalInfo = $payment->getPayment()->getAdditionalInformation();
        if (!empty($additionalInfo[Constants::APM_BRAND_NAME])) {
            $this->logger->info('APM BRAND :: ' . $additionalInfo[Constants::APM_BRAND_NAME]);
            $paymentType = $this->dataHelper->getApmPaymentType($additionalInfo[Constants::APM_BRAND_NAME]);
        } else {
            $this->logger->error('APM Brand in Additional Info is blank ');
            $paymentType = Constants::DEFAULT_PAYMENT_TYPE_APM;
        }
        return [
            Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE => $paymentType,
        ];
    }
}
