<?php

namespace Aci\Payment\Observer;

use Aci\Payment\Model\Ui\AciApmConfigProvider;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

/**
 * Set quote as active
 */
class SubmitQuoteObserver implements ObserverInterface
{
    /**
     * @var array<mixed>
     */
    protected array $aciPaymentMethods = [
        AciCcConfigProvider::CODE,
        AciApmConfigProvider::CODE
    ];

    /**
     * Keep cart active
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();/** @phpstan-ignore-line */
        $paymentMethod = strtolower($quote->getPayment()->getMethod());
        if (in_array($paymentMethod, $this->aciPaymentMethods)) {
            // Keep cart active until such actions are taken
            $quote->setIsActive(true);
        }
    }
}
