<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use Magento\Framework\Exception\LocalizedException;
use TryzensIgnite\Subscription\Api\Data\RecurringOrderInterface;
use TryzensIgnite\Subscription\Api\RecurringOrderRepositoryInterface;
use TryzensIgnite\Subscription\Model\ManageSubscriptionFrequency;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TryzensIgnite\Subscription\Model\RecurringOrder;

/**
 * Class SubscriptionScheduleDataBuilder
 * Builds subscription scheduler data
 */
class SubscriptionScheduleDataBuilder implements BuilderInterface
{

    /**
     * @var ManageSubscriptionFrequency
     */
    private ManageSubscriptionFrequency $frequencySession;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @param ManageSubscriptionFrequency $frequencySession
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     */
    public function __construct(
        ManageSubscriptionFrequency $frequencySession,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
    ) {
        $this->frequencySession = $frequencySession;
        $this->recurringOrderRepository = $recurringOrderRepository;
    }

    /**
     * Builds scheduler API call data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $registrationId = $buildSubject[Constants::KEY_REGISTRATION_ID];
        if (!$registrationId) {
            $registrationId =  $buildSubject['requestParams']
            [Constants::KEY_NOTIFICATION_PAYLOAD]
            [Constants::KEY_REGISTRATION_ID];
        }
        /** @var RecurringOrder $recurringOrder */
        $recurringOrder = $this->recurringOrderRepository
            ->getByRegistrationId($registrationId);
        $jobExpression = $recurringOrder->getJobExpression();
        if (!$jobExpression) {
            $jobExpression = $this->frequencySession->getSubscriptionJobExpressionFromSession();
        }
        return [
            Constants::KEY_STANDING_INSTRUCTION_MODE => Constants::SCHEDULER_STANDING_INSTRUCTION_MODE,
            Constants::KEY_STANDING_INSTRUCTION_TYPE => Constants::STANDING_INSTRUCTION_TYPE,
            Constants::KEY_STANDING_INSTRUCTION_SOURCE => Constants::SCHEDULER_STANDING_INSTRUCTION_SOURCE,
            Constants::KEY_STANDING_INSTRUCTION_RECURRING_TYPE => Constants::STANDING_INSTRUCTION_RECURRING_TYPE,
            Constants::KEY_JOB_EXPRESSION => $jobExpression,
        ];
    }
}
