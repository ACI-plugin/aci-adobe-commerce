<?php

namespace Aci\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Aci\Payment\Api\RecurringOrderHistoryRepositoryInterface;
use Aci\Payment\Api\Data\RecurringOrderHistoryInterfaceFactory;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;

/**
 * SubscriptionManagement - Subscription Data Operation
 */
class SubscriptionManagement
{
    /**
     * @var RecurringOrderHistoryRepositoryInterface
     */
    protected RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    protected RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var RecurringOrderHistoryInterfaceFactory
     */
    protected RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory;

    /**
     * @param RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory
     */
    public function __construct(
        RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory
    ) {
        $this->recurringOrderHistoryRepository = $recurringOrderHistoryRepository;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrderHistoryInterfaceFactory = $recurringOrderHistoryInterfaceFactory;
    }

    /**
     * Update Last Order ID in Subscription Table
     *
     * @param int $lastOrderId
     * @param string $registrationId
     * @param string $newIncrementId
     * @throws LocalizedException
     */
    public function updateLastOrderId(int $lastOrderId, string $registrationId, string $newIncrementId): void
    {
        $recurringOrder = $this->recurringOrderRepository->getByRegistrationId($registrationId);
        $recurringOrder->setLastOrderId($lastOrderId);
        $recurringOrder->setLastIncrementId($newIncrementId);
        $this->recurringOrderRepository->save($recurringOrder);
    }

    /**
     * Insert New Order ID with Subscription ID in History table
     *
     * @param int $newOrderId
     * @param string $subscriptionId
     * @throws LocalizedException
     */
    public function insertSubscriptionOrder(int $newOrderId, string $subscriptionId): void
    {
        $recurringOrderHistory = $this->recurringOrderHistoryInterfaceFactory->create();
        $recurringOrderHistory->setOrderId($newOrderId);
        $recurringOrderHistory->setRegistrationId($subscriptionId);
        $this->recurringOrderHistoryRepository->save($recurringOrderHistory);
    }

    /**
     * Get Order ID by registration ID
     *
     * @param string $registrationId
     * @return int|null
     * @throws LocalizedException
     */
    public function getOrderIdFromRegistrationId(string $registrationId): int|null
    {
        try {
            $recurringOrder = $this->recurringOrderRepository->getByRegistrationId($registrationId);
        } catch (LocalizedException $e) {
            return null;
        }
        return $recurringOrder->getOrderId();
    }
}
