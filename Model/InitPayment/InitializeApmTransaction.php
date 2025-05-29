<?php
namespace Aci\Payment\Model\InitPayment;

use TryzensIgnite\Base\Model\Api\RequestManager;
use TryzensIgnite\Base\Api\QuoteManagerInterface;
use TryzensIgnite\Base\Logger\Logger;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Aci\Payment\Helper\Constants;

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
     * @param Logger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManagerInterface $quoteManager,
        Logger $logger,
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
