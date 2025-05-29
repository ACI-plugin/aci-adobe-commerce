<?php
namespace Aci\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Aci\Payment\Helper\Constants;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Additional Information to order payment - ACI_APM payment
 */
class AdditionalPaymentData extends AbstractDataAssignObserver
{

    /**
     * Update APM method name to payment object
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        $aciPaymentData = [];
        $paymentInfo = $this->readPaymentModelArgument($observer);
        if (is_array($additionalData) && isset($additionalData[Constants::APM_BRAND_NAME])) {
            $aciPaymentData[Constants::APM_BRAND_NAME] = $additionalData[Constants::APM_BRAND_NAME];

            if ($transactionId = $paymentInfo->getAdditionalInformation(Constants::TRANSACTION_ID)) {
                $aciPaymentData[Constants::TRANSACTION_ID] = $transactionId;
            }
        }

        if ($aciPaymentData) {
            foreach ($aciPaymentData as $key => $value) {
                $paymentInfo->setAdditionalInformation($key, $value);
            }
        }
    }
}
