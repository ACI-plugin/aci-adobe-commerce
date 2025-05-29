<?php

namespace Aci\Payment\Controller\Payment;

use Aci\Payment\Logger\AciCcLogger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Aci\Payment\Model\InitPayment\InitializeCcTransaction;
use Magento\Framework\Data\Form\FormKey\Validator;
use TryzensIgnite\Base\Controller\Payment\InitTransaction as IgniteInitTransaction;

/**
 * Init payment transaction
 */
class CcInitPayment extends IgniteInitTransaction
{
    /**
     * CcInitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param InitializeCcTransaction $initTransactionModel
     * @param Validator $formKeyValidator
     * @param AciCcLogger $logger
     */
    //phpcs:disable
    public function __construct(
        JsonFactory             $resultJsonFactory,
        ResultRedirectFactory   $resultRedirectFactory,
        Context                 $context,
        InitializeCcTransaction $initTransactionModel,
        Validator               $formKeyValidator,
        AciCcLogger             $logger
    ) {
        parent::__construct(
            $resultJsonFactory,
            $resultRedirectFactory,
            $context,
            $initTransactionModel,
            $formKeyValidator,
            $logger
        );
    }
}
