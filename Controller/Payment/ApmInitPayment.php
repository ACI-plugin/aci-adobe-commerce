<?php

namespace Aci\Payment\Controller\Payment;

use TryzensIgnite\Base\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Aci\Payment\Model\InitPayment\InitializeApmTransaction;
use Magento\Framework\Data\Form\FormKey\Validator;
use TryzensIgnite\Base\Controller\Payment\InitTransaction as IgniteInitTransaction;

/**
 * Init payment transaction
 */
class ApmInitPayment extends IgniteInitTransaction
{
    /**
     * ApmInitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param InitializeApmTransaction $initTransactionModel
     * @param Validator $formKeyValidator
     * @param Logger $logger
     */
    //phpcs:disable
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        InitializeApmTransaction $initTransactionModel,
        Validator $formKeyValidator,
        Logger $logger
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
