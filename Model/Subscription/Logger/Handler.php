<?php
namespace Aci\Payment\Model\Subscription\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Log handler
 */
class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/aci_subscription.log';

    /**
     * Gets logs file location
     *
     * @return string
     */
    public function getLogLocation(): string
    {
        return $this->fileName;
    }
}
