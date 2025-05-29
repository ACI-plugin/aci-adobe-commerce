<?php
namespace Aci\Payment\Model\ResourceModel\RecurringOrderHistory;

use Aci\Payment\Model\RecurringOrderHistory;
use Aci\Payment\Model\ResourceModel\RecurringOrderHistory as ResourceRecurringOrderHistory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix for recurring order
     *
     * @var string
     */
    protected $_eventPrefix = 'recurring_orders_collection';

    /**
     * Event object key for recurring order
     *
     * @var string
     */
    protected $_eventObject = 'recurring_orders_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(RecurringOrderHistory::class, ResourceRecurringOrderHistory::class);
    }
}
