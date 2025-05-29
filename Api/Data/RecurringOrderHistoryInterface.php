<?php
namespace Aci\Payment\Api\Data;

/**
 * Recurring Order History interface - SET and GET methods
 */
interface RecurringOrderHistoryInterface
{
    public const ORDER_ID              = 'order_id';
    public const REGISTRATION_ID       = 'registration_id';

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Get Registration ID
     *
     * @return string|null
     */
    public function getRegistrationId(): ?string;

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return RecurringOrderHistoryInterface
     */
    public function setOrderId(int $orderId): RecurringOrderHistoryInterface;

    /**
     * Set Registration ID
     *
     * @param string $registrationId
     * @return RecurringOrderHistoryInterface
     */
    public function setRegistrationId(string $registrationId): RecurringOrderHistoryInterface;
}
