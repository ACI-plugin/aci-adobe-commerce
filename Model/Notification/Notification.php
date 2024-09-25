<?php

namespace Aci\Payment\Model\Notification;

use TryzensIgnite\Notification\Cron\Process\Notification as IgniteCronNotification;

/**
 * Update order on running cron with notification data
 */
class Notification extends IgniteCronNotification
{

    /**
     * Condition check to retry without limit
     *
     * @return bool
     */
    public function shouldRetryInfinitely() : bool
    {
        return true;
    }
}
