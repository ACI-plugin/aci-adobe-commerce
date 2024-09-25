<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use TryzensIgnite\Subscription\Model\ManageSubscriptionFrequency;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class SubscriptionDataBuilder
 * Builds subscription initial api call data
 */
class SubscriptionDataBuilder implements BuilderInterface
{

    /**
     * @var ManageSubscriptionFrequency
     */
    private ManageSubscriptionFrequency $frequencySession;

    /**
     * @param ManageSubscriptionFrequency $frequencySession
     */
    public function __construct(
        ManageSubscriptionFrequency $frequencySession,
    ) {
        $this->frequencySession = $frequencySession;
    }

    /**
     * Builds subscription order creation related data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();
        if ($order->getCustomerId()) {
            if ($this->frequencySession->getSubscriptionFrequencyFromSession()) {
                return [
                    Constants::KEY_CREATE_REGISTRATION => 'true',
                    Constants::KEY_STANDING_INSTRUCTION_MODE => Constants::REGISTRATION_STANDING_INSTRUCTION_MODE,
                    Constants::KEY_STANDING_INSTRUCTION_TYPE => Constants::STANDING_INSTRUCTION_TYPE,
                    Constants::KEY_STANDING_INSTRUCTION_SOURCE => Constants::REGISTRATION_STANDING_INSTRUCTION_SOURCE,
                    Constants::KEY_STANDING_INSTRUCTION_RECURRING_TYPE => Constants::STANDING_INSTRUCTION_RECURRING_TYPE
                ];
            }
        }
        return[];
    }
}
