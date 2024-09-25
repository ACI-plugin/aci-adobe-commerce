<?php
namespace Aci\Payment\Model;

use TryzensIgnite\Common\Model\Api\RequestManager;
use TryzensIgnite\Common\Api\QuoteManagerInterface;
use Aci\Payment\Logger\AciApmLogger;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use TryzensIgnite\Common\Helper\Constants;

/**
 *
 * Process InitTransaction Request for Apm Payment
 */
class InitializeApmTransaction extends RequestManager
{
    /**
     * CreateTransaction constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManagerInterface $quoteManager
     * @param AciApmLogger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManagerInterface $quoteManager,
        AciApmLogger $logger,
        string $commandName = Constants::INITIALIZE_TRANSACTION
    ) {
        parent::__construct(
            $commandPool,
            $paymentDataObjectFactory,
            $quoteManager,
            $logger,
            $commandName
        );
    }
}
