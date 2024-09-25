<?php

namespace Aci\Payment\Controller\Payment;

use Aci\Payment\Logger\AciApmLogger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Aci\Payment\Model\InitializeApmTransaction;
use Magento\Framework\Data\Form\FormKey\Validator;
use TryzensIgnite\Common\Controller\Payment\InitPayment;

/**
 * Init payment transaction
 */
class ApmInitPayment extends InitPayment
{
    /**
     * ApmInitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param InitializeApmTransaction $initTransactionModel
     * @param Validator $formKeyValidator
     * @param AciApmLogger $logger
     */
    //phpcs:disable
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        InitializeApmTransaction $initTransactionModel,
        Validator $formKeyValidator,
        AciApmLogger $logger
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
