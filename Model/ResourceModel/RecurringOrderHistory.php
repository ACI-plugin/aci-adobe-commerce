<?php
namespace Aci\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Recurring Order history resource
 */
class RecurringOrderHistory extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('aci_payment_subscription_orders', 'entity_id');
    }
}
