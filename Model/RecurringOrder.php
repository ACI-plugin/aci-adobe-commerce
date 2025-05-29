<?php
namespace Aci\Payment\Model;

use Aci\Payment\Api\Data\RecurringOrderInterface;
use Magento\Framework\Model\AbstractModel;
use Aci\Payment\Model\ResourceModel\RecurringOrder as ResourceRecurringOrder;

/**
 * Recurring Order Model
 */
class RecurringOrder extends AbstractModel implements RecurringOrderInterface
{

    public const SUB_STATUS_ACTIVE          =   1;
    public const SUB_STATUS_CANCELLED       =   0;
    public const SUB_STATUS_PENDING         =   2;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceRecurringOrder::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getRecurringOrderId(): ?int
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set Increment ID
     *
     * @return string|null
     */
    public function getIncrementId(): ?string
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * Set Last Increment ID
     *
     * @return string|null
     */
    public function getLastIncrementId(): ?string
    {
        return $this->getData(self::LAST_INCREMENT_ID);
    }

    /**
     * Get Last Order ID
     *
     * @return int
     */
    public function getLastOrderId(): int
    {
        return $this->getData(self::LAST_ORDER_ID);
    }

    /**
     * Get Subscription ID
     *
     * @return string|null
     */
    public function getSubscriptionId(): ?string
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get Registration ID
     *
     * @return string|null
     */
    public function getRegistrationId(): ?string
    {
        return $this->getData(self::REGISTRATION_ID);
    }

    /**
     * Get Job Expression
     *
     * @return string|null
     */
    public function getJobExpression(): ?string
    {
        return $this->getData(self::JOB_EXPRESSION);
    }

    /**
     * Get Test Mode
     *
     * @return string|null
     */
    public function getTestMode(): ?string
    {
        return $this->getData(self::TEST_MODE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return RecurringOrderInterface
     */
    public function setRecurringOrderId(int $id): RecurringOrderInterface
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return RecurringOrderInterface
     */
    public function setCustomerId(int $customerId): RecurringOrderInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return RecurringOrderInterface
     */
    public function setOrderId(int $orderId): RecurringOrderInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Increment ID
     *
     * @param string $incrementId
     * @return RecurringOrderInterface
     */
    public function setIncrementId(string $incrementId): RecurringOrderInterface
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * Set Last Increment ID
     *
     * @param string $incrementId
     * @return RecurringOrderInterface
     */
    public function setLastIncrementId(string $incrementId): RecurringOrderInterface
    {
        return $this->setData(self::LAST_INCREMENT_ID, $incrementId);
    }

    /**
     * Set Last Order ID
     *
     * @param int $lastOrderId
     * @return RecurringOrderInterface
     */
    public function setLastOrderId(int $lastOrderId): RecurringOrderInterface
    {
        return $this->setData(self::LAST_ORDER_ID, $lastOrderId);
    }

    /**
     * Set Subscription ID
     *
     * @param string $subscriptionId
     * @return RecurringOrderInterface
     */
    public function setSubscriptionId(string $subscriptionId): RecurringOrderInterface
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return RecurringOrderInterface
     */
    public function setStatus(int $status): RecurringOrderInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Registration ID
     *
     * @param string $registrationId
     * @return RecurringOrderInterface
     */
    public function setRegistrationId(string $registrationId): RecurringOrderInterface
    {
        return $this->setData(self::REGISTRATION_ID, $registrationId);
    }

    /**
     * Set Job Expression
     *
     * @param string $jobExpression
     * @return RecurringOrderInterface
     */
    public function setJobExpression(string $jobExpression): RecurringOrderInterface
    {
        return $this->setData(self::JOB_EXPRESSION, $jobExpression);
    }

    /**
     * Set Test Mode
     *
     * @param string $testMode
     * @return RecurringOrderInterface
     */
    public function setTestMode(string $testMode): RecurringOrderInterface
    {
        return $this->setData(self::TEST_MODE, $testMode);
    }
}
