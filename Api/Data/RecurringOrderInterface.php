<?php
namespace Aci\Payment\Api\Data;

/**
 * Recurring Order interface - SET and GET methods
 */
interface RecurringOrderInterface
{
    public const ENTITY_ID             = 'entity_id';
    public const CUSTOMER_ID           = 'customer_id';
    public const ORDER_ID              = 'order_id';
    public const INCREMENT_ID          = 'increment_id';
    public const LAST_INCREMENT_ID     = 'last_increment_id';
    public const LAST_ORDER_ID         = 'last_order_id';
    public const SUBSCRIPTION_ID       = 'subscription_id';
    public const STATUS                = 'status';
    public const REGISTRATION_ID       = 'registration_id';

    public const JOB_EXPRESSION        = 'job_expression';

    public const TEST_MODE             = 'test_mode';
    public const RECURRING_UNIT        = 'recurring_unit';
    public const RECURRING_FREQUENCY   = 'recurring_frequency';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getRecurringOrderId(): ?int;

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Get Increment ID
     *
     * @return string|null
     */
    public function getIncrementId(): ?string;

    /**
     * Get Last Increment ID
     *
     * @return string|null
     */
    public function getLastIncrementId(): ?string;

    /**
     * Get Last Order ID
     *
     * @return int
     */
    public function getLastOrderId(): int;

    /**
     * Get Subscription ID
     *
     * @return string|null
     */
    public function getSubscriptionId(): ?string;

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get Registration ID
     *
     * @return string|null
     */
    public function getRegistrationId(): ?string;

    /**
     * Get Job expression
     *
     * @return string|null
     */
    public function getJobExpression(): ?string;

    /**
     * Get Test Mode
     *
     * @return string|null
     */
    public function getTestMode(): ?string;

    /**
     * Set ID
     *
     * @param int $id
     * @return RecurringOrderInterface
     */
    public function setRecurringOrderId(int $id): RecurringOrderInterface;

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return RecurringOrderInterface
     */
    public function setCustomerId(int $customerId): RecurringOrderInterface;

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return RecurringOrderInterface
     */
    public function setOrderId(int $orderId): RecurringOrderInterface;

    /**
     * Set Increment ID
     *
     * @param string $incrementId
     * @return RecurringOrderInterface
     */
    public function setIncrementId(string $incrementId): RecurringOrderInterface;

    /**
     * Set Last Increment ID
     *
     * @param string $incrementId
     * @return RecurringOrderInterface
     */
    public function setLastIncrementId(string $incrementId): RecurringOrderInterface;

    /**
     * Set Last Order ID
     *
     * @param int $lastOrderId
     * @return RecurringOrderInterface
     */
    public function setLastOrderId(int $lastOrderId): RecurringOrderInterface;

    /**
     * Set Subscription ID
     *
     * @param string $subscriptionId
     * @return RecurringOrderInterface
     */
    public function setSubscriptionId(string $subscriptionId): RecurringOrderInterface;

    /**
     * Set Status
     *
     * @param int $status
     * @return RecurringOrderInterface
     */
    public function setStatus(int $status): RecurringOrderInterface;

    /**
     * Set Registration ID
     *
     * @param string $registrationId
     * @return RecurringOrderInterface
     */
    public function setRegistrationId(string $registrationId): RecurringOrderInterface;

    /**
     * Set Job Expression
     *
     * @param string $jobExpression
     * @return RecurringOrderInterface
     */
    public function setJobExpression(string $jobExpression): RecurringOrderInterface;

    /**
     * Set Test Mode
     *
     * @param string $testMode
     * @return RecurringOrderInterface
     */
    public function setTestMode(string $testMode): RecurringOrderInterface;
}
