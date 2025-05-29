<?php
namespace Aci\Payment\Model\Subscription;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Model\RecurringOrderRepository;

/**
 * Class for subscription's custom transactions management
 */
class ManageSubscription
{
    public const KEY_SUBSCRTPTION_ID = 'subscription_id';
    /**
     * @var RecurringOrderRepository
     */
    private RecurringOrderRepository $recurringOrderRepository;

    /**
     * @param RecurringOrderRepository $recurringOrderRepository
     */
    public function __construct(
        RecurringOrderRepository $recurringOrderRepository
    ) {
        $this->recurringOrderRepository = $recurringOrderRepository;
    }

    /**
     * Update Subscription Status
     *
     * @param string $subscriptionId
     * @param int $status
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function updateSubscriptionStatus(string $subscriptionId, int $status): void
    {
        $subscriptionModel = $this->recurringOrderRepository->getBySubscriptionId($subscriptionId);
        if ($subscriptionModel->getId()) {
            $subscriptionModel->setStatus($status);
            $this->recurringOrderRepository->save($subscriptionModel);
        }
    }

    /**
     * Get subscription id key
     *
     * @return string
     */
    public function getSubscriptionIdKey(): string
    {
        return self::KEY_SUBSCRTPTION_ID;
    }
}
