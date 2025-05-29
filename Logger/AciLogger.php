<?php
namespace Aci\Payment\Logger;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use DateTimeZone;
use TryzensIgnite\Base\Logger\Logger as BaseLogger;

/**
 * Logger to save the log details in log file
 */
class AciLogger extends BaseLogger
{
    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

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
        $this->paymentConfig = $paymentConfig;
        parent::__construct($paymentConfig, $name, $handlers, $processors, $timezone);
    }

    /**
     * Logging info function
     *
     * @param string $message
     * @param array<mixed> $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if ($this->paymentConfig->isActive() && $this->paymentConfig->isDebugEnabled()) {
            parent::addRecord(static::INFO, (string)$message, $context);
        }
    }
}
