<?php
namespace Aci\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Recurring Order mysql resource
 */
class RecurringOrder extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('aci_payment_subscription', 'entity_id');
    }
}
