<?php
namespace Aci\Payment\Logger;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use DateTimeZone;

/**
 * Logger to save the log details in log file
 */
class AciCcLogger extends AciLogger
{
    /**
     * @param AciGenericPaymentConfig $paymentConfig
     * @param string $name
     * @param array<mixed> $handlers
     * @param array<mixed> $processors
     * @param DateTimeZone|null $timezone
     */
    //phpcs:disable
    public function __construct(
        AciGenericPaymentConfig $paymentConfig,
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($paymentConfig, $name, $handlers, $processors, $timezone);
    }
}
