<?php
namespace Aci\Payment\Model;

use Aci\Payment\Api\Data\RecurringOrderHistoryInterface;
use Magento\Framework\Model\AbstractModel;
use Aci\Payment\Model\ResourceModel\RecurringOrderHistory as ResourceRecurringOrderHistory;

/**
 * Recurring Order History Model
 */
class RecurringOrderHistory extends AbstractModel implements RecurringOrderHistoryInterface
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceRecurringOrderHistory::class);
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
     * Get Registration ID
     *
     * @return string|null
     */
    public function getRegistrationId(): ?string
    {
        return $this->getData(self::REGISTRATION_ID);
    }

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return RecurringOrderHistoryInterface
     */
    public function setOrderId(int $orderId): RecurringOrderHistoryInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Registration ID
     *
     * @param string $registrationId
     * @return RecurringOrderHistoryInterface
     */
    public function setRegistrationId(string $registrationId): RecurringOrderHistoryInterface
    {
        return $this->setData(self::REGISTRATION_ID, $registrationId);
    }
}
