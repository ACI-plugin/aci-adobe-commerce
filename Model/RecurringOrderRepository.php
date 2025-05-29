<?php
namespace Aci\Payment\Model;

use Aci\Payment\Api\Data\RecurringOrderInterface;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;
use Aci\Payment\Model\ResourceModel\RecurringOrder as ResourceRecurringOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Model\ResourceModel\RecurringOrder\CollectionFactory;

/**
 * Recurring Order repository - Definition for CRUD operation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecurringOrderRepository implements RecurringOrderRepositoryInterface
{
    /**
     * @var ResourceRecurringOrder
     */
    protected ResourceRecurringOrder $resource;

    /**
     * @var RecurringOrderFactory
     */
    protected RecurringOrderFactory $recurringOrderFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @param ResourceRecurringOrder $resource
     * @param RecurringOrderFactory $recurringOrderFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceRecurringOrder $resource,
        RecurringOrderFactory $recurringOrderFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->recurringOrderFactory = $recurringOrderFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save Recurring Order data
     *
     * @param RecurringOrderInterface $recurringOrder
     * @return RecurringOrder
     * @throws CouldNotSaveException
     */
    public function save(RecurringOrderInterface $recurringOrder): RecurringOrder
    {
        try {
            /** @var RecurringOrder $recurringOrder */
            $this->resource->save($recurringOrder);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the recurring order: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the recurring order: %1', __('Something went wrong while saving the order.')),
                $exception
            );
        }
        return $recurringOrder;
    }

    /**
     * Load Recurring Order data by given ID
     *
     * @param int $recurringOrderId
     * @return RecurringOrder
     * @throws NoSuchEntityException
     */
    public function getById(int $recurringOrderId): RecurringOrderInterface
    {
        $recurringOrder = $this->recurringOrderFactory->create();
        $this->resource->load($recurringOrder, $recurringOrderId, 'entity_id');
        if (!$recurringOrder->getRecurringOrderId()) {
            throw new NoSuchEntityException(
                __('The Recurring Order with the "%1" ID doesn\'t exist.', $recurringOrderId)
            );
        }

        return $recurringOrder;
    }

    /**
     * Load Recurring Order data by given order ID
     *
     * @param int $orderId
     * @return RecurringOrderInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): RecurringOrderInterface
    {
        $recurringOrder = $this->recurringOrderFactory->create();
        $this->resource->load($recurringOrder, $orderId, RecurringOrderInterface::ORDER_ID);
        if (!$recurringOrder->getRecurringOrderId()) {
            throw new NoSuchEntityException(
                __('The Recurring Order with the "%1" order ID doesn\'t exist.', $orderId)
            );
        }
        return $recurringOrder;
    }

    /**
     * Get data by subscription id
     *
     * Retrieve Recurring Order By Subscription ID
     *
     * @param string $subscriptionId
     * @return RecurringOrder
     * @throws NoSuchEntityException
     */
    public function getBySubscriptionId(string $subscriptionId): RecurringOrderInterface
    {
        $recurringOrder = $this->recurringOrderFactory->create();
        $this->resource->load($recurringOrder, $subscriptionId, 'subscription_id');
        if (!$recurringOrder->getRecurringOrderId()) {
            throw new NoSuchEntityException(
                __('The Recurring Order with the "%1" subscription ID doesn\'t exist.', $subscriptionId)
            );
        }

        return $recurringOrder;
    }

    /**
     * Retrieve Recurring Order By Registration ID
     *
     * @param string $registrationId
     * @return RecurringOrder
     * @throws NoSuchEntityException
     */
    public function getByRegistrationId(string $registrationId): RecurringOrderInterface
    {
        $recurringOrder = $this->recurringOrderFactory->create();
        $this->resource->load($recurringOrder, $registrationId, 'registration_id');
        if (!$recurringOrder->getRecurringOrderId()) {
            throw new NoSuchEntityException(
                __('The Recurring Order with the "%1" registration ID doesn\'t exist.', $registrationId)
            );
        }

        return $recurringOrder;
    }

    /**
     * Delete Recurring Order
     *
     * @param RecurringOrderInterface $recurringOrder
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RecurringOrderInterface $recurringOrder): bool
    {
        try {
            /** @var RecurringOrder $recurringOrder */
            $this->resource->delete($recurringOrder);
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
     * @param int $recurringOrderId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $recurringOrderId): bool
    {
        return $this->delete($this->getById($recurringOrderId));
    }
}
