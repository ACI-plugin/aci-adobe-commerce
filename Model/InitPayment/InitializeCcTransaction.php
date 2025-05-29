<?php
namespace Aci\Payment\Model\InitPayment;

use TryzensIgnite\Base\Model\Api\RequestManager;
use TryzensIgnite\Base\Api\QuoteManagerInterface;
use Aci\Payment\Logger\AciCcLogger;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Aci\Payment\Helper\Constants;

/**
 *
 * Process InitTransaction Request
 */
class InitializeCcTransaction extends RequestManager
{
    /**
     * CreateTransaction constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManagerInterface $quoteManager
     * @param AciCcLogger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface     $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManagerInterface    $quoteManager,
        AciCcLogger              $logger,
        string                   $commandName = Constants::INITIALIZE_TRANSACTION
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
