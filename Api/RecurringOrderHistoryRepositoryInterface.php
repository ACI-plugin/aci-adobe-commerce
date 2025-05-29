<?php
namespace Aci\Payment\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Api\Data\RecurringOrderHistoryInterface;

/**
 * Recurring Order History CRUD interface.
 */
interface RecurringOrderHistoryRepositoryInterface
{
    /**
     * Save Recurring Order.
     *
     * @param RecurringOrderHistoryInterface $recurringOrderHistory
     * @return RecurringOrderHistoryInterface
     * @throws LocalizedException
     */
    public function save(RecurringOrderHistoryInterface $recurringOrderHistory): RecurringOrderHistoryInterface;

    /**
     * Retrieve Recurring Order By ID.
     *
     * @param int $recurringOrderHistoryId
     * @return RecurringOrderHistoryInterface
     * @throws LocalizedException
     */
    public function getById(int $recurringOrderHistoryId): RecurringOrderHistoryInterface;

    /**
     * Delete Recurring Order.
     *
     * @param RecurringOrderHistoryInterface $recurringOrderHistory
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RecurringOrderHistoryInterface $recurringOrderHistory): bool;

    /**
     * Delete Recurring Order by ID.
     *
     * @param int $recurringOrderHistoryId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $recurringOrderHistoryId): bool;
}
