<?php
namespace Aci\Payment\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Api\Data\RecurringOrderInterface;

/**
 * Recurring Order CRUD interface.
 */
interface RecurringOrderRepositoryInterface
{
    /**
     * Save Recurring Order.
     *
     * @param RecurringOrderInterface $recurringOrder
     * @return RecurringOrderInterface
     * @throws LocalizedException
     */
    public function save(RecurringOrderInterface $recurringOrder): RecurringOrderInterface;

    /**
     * Retrieve Recurring Order By ID.
     *
     * @param int $recurringOrderId
     * @return RecurringOrderInterface
     * @throws LocalizedException
     */
    public function getById(int $recurringOrderId): RecurringOrderInterface;

    /**
     * Retrieve Recurring Order By order ID.
     *
     * @param int $orderId
     * @return RecurringOrderInterface
     * @throws LocalizedException
     */
    public function getByOrderId(int $orderId): RecurringOrderInterface;

    /**
     * Retrieve Recurring Order By Subscription ID.
     *
     * @param string $subscriptionId
     * @return RecurringOrderInterface
     * @throws LocalizedException
     */
    public function getBySubscriptionId(string $subscriptionId): RecurringOrderInterface;

    /**
     * Retrieve Recurring Order By Registration ID.
     *
     * @param string $registrationId

     * @return RecurringOrderInterface
     * @throws LocalizedException
     */
    public function getByRegistrationId(string $registrationId): RecurringOrderInterface;

    /**
     * Delete Recurring Order.
     *
     * @param RecurringOrderInterface $recurringOrder
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RecurringOrderInterface $recurringOrder): bool;

    /**
     * Delete Recurring Order by ID.
     *
     * @param int $recurringOrderId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $recurringOrderId): bool;
}
