<?php
namespace Aci\Payment\Model;

use Aci\Payment\Api\Data\RecurringOrderHistoryInterface;
use Aci\Payment\Api\RecurringOrderHistoryRepositoryInterface;
use Aci\Payment\Model\ResourceModel\RecurringOrderHistory as ResourceRecurringOrderHistory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Recurring Order history repository - Definition for CRUD operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecurringOrderHistoryRepository implements RecurringOrderHistoryRepositoryInterface
{
    /**
     * @var ResourceRecurringOrderHistory
     */
    protected ResourceRecurringOrderHistory $resource;

    /**
     * @var RecurringOrderHistoryFactory
     */
    protected RecurringOrderHistoryFactory $recurringOrderFactory;

    /**
     * @param ResourceRecurringOrderHistory $resource
     * @param RecurringOrderHistoryFactory $recurringOrderFactory
     */
    public function __construct(
        ResourceRecurringOrderHistory $resource,
        RecurringOrderHistoryFactory $recurringOrderFactory
    ) {
        $this->resource = $resource;
        $this->recurringOrderFactory = $recurringOrderFactory;
    }

    /**
     * Save Recurring Order data
     *
     * @param RecurringOrderHistoryInterface $recurringOrderHistory
     * @return RecurringOrderHistory
     * @throws CouldNotSaveException
     */
    public function save(RecurringOrderHistoryInterface $recurringOrderHistory): RecurringOrderHistory
    {
        try {
            /** @var RecurringOrderHistory $recurringOrderHistory */
            $this->resource->save($recurringOrderHistory);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the recurring order: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the recurring order: %1',
                    __('Something went wrong while saving the recurring order history.')
                ),
                $exception
            );
        }
        return $recurringOrderHistory;
    }

    /**
     * Load Recurring Order data by given ID
     *
     * @param int $recurringOrderHistoryId
     * @return RecurringOrderHistory
     * @throws NoSuchEntityException
     */
    public function getById(int $recurringOrderHistoryId): RecurringOrderHistoryInterface
    {
        $recurringOrder = $this->recurringOrderFactory->create();
        $this->resource->load($recurringOrder, $recurringOrderHistoryId, 'entity_id');
        if (!$recurringOrder->getId()) {
            throw new NoSuchEntityException(
                __('The Recurring Order History with the "%1" ID doesn\'t exist.', $recurringOrderHistoryId)
            );
        }

        return $recurringOrder;
    }

    /**
     * Delete Recurring Order
     *
     * @param RecurringOrderHistoryInterface $recurringOrderHistory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RecurringOrderHistoryInterface $recurringOrderHistory): bool
    {
        try {
            /** @var RecurringOrderHistory $recurringOrderHistory */
            $this->resource->delete($recurringOrderHistory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Recurring Order: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Recurring Order by given ID
     *
     * @param int $recurringOrderHistoryId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $recurringOrderHistoryId): bool
    {
        return $this->delete($this->getById($recurringOrderHistoryId));
    }
}
