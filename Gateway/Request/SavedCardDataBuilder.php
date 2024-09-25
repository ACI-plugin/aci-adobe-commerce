<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Model\Data\SavedCard as SavedCardData;
use Aci\Payment\Model\Ui\AciCcConfigProvider;

/**
 * Class CustomerDataBuilder
 * Builds customer data
 */
class SavedCardDataBuilder implements BuilderInterface
{
    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var SavedCardData
     */
    private SavedCardData $savedCardData;

    /**
     * @param Utilities $utilities
     * @param SavedCardData $savedCardData
     */
    public function __construct(
        Utilities $utilities,
        SavedCardData $savedCardData
    ) {
        $this->utilities = $utilities;
        $this->savedCardData = $savedCardData;
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
        if ($order->getCustomerId()) {
            $savedCardsArray = [];
            $savedCards = $this->savedCardData->getCustomerSavedCards(AciCcConfigProvider::CODE);
            if ($savedCards) {
                foreach ($savedCards as $key => $value) {
                    $savedCardsArray["registrations[$key].id"] = $value['value'];
                }
            }
            return $this->utilities->removeNullValues($savedCardsArray);
        }
        return[];
    }
}
