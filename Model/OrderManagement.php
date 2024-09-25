<?php

namespace Aci\Payment\Model;

use Aci\Payment\Helper\Constants;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use TryzensIgnite\Subscription\Logger\Logger;
use TryzensIgnite\Subscription\Model\Config as SubscriptionModelConfig;
use TryzensIgnite\Subscription\Model\EmailManagement;
use TryzensIgnite\Subscription\Model\OrderManagement as IgniteSubscriptionOrderManagement;
use TryzensIgnite\Subscription\Model\SubscriptionManagement;
use TryzensIgnite\Subscription\Model\TransactionManagement;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Model\TransactionManagement as AciTransactionManagement;
use Aci\Payment\Model\Ui\AciGenericConfigProvider;
use TryzensIgnite\Subscription\Api\RecurringOrderRepositoryInterface;

/**
 * Manage order process
 */
class OrderManagement extends IgniteSubscriptionOrderManagement
{
    public const KEY_RESULT_DESCRIPTION     =   'code';

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var AciTransactionManagement
     */
    protected AciTransactionManagement $aciTransactionManagement;

    /**
     * @param Utilities $utilities
     * @param AciTransactionManagement $aciTransactionManagement
     * @param OrderManagementInterface $orderManagement
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param BuilderInterface $paymentTransactionBuilder
     * @param OrderFactory $orderFactory
     * @param CheckoutSession $checkoutSession
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param QuoteFactory $quoteFactory
     * @param CartManagementInterface $cartManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailManagement $emailManagement
     * @param TransactionManagement $transactionManagement
     * @param SubscriptionModelConfig $subscriptionConfig
     * @param SubscriptionManagement $subscriptionManagement
     * @param CartRepositoryInterface $cartRepository
     * @param Logger $logger
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     */
    public function __construct(
        Utilities $utilities,
        AciTransactionManagement $aciTransactionManagement,
        OrderManagementInterface $orderManagement,
        OrderStatusHistoryFactory $orderStatusHistoryFactory,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        BuilderInterface $paymentTransactionBuilder,
        OrderFactory $orderFactory,
        CheckoutSession $checkoutSession,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        QuoteFactory $quoteFactory,
        CartManagementInterface $cartManagement,
        CustomerRepositoryInterface $customerRepository,
        EmailManagement $emailManagement,
        TransactionManagement $transactionManagement,
        SubscriptionModelConfig $subscriptionConfig,
        SubscriptionManagement $subscriptionManagement,
        CartRepositoryInterface $cartRepository,
        Logger $logger,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
    ) {
        $this->utilities = $utilities;
        $this->aciTransactionManagement = $aciTransactionManagement;
        parent::__construct(
            $orderManagement,
            $orderStatusHistoryFactory,
            $orderRepository,
            $transactionRepository,
            $orderPaymentRepository,
            $paymentTransactionBuilder,
            $orderFactory,
            $checkoutSession,
            $invoiceService,
            $invoiceSender,
            $transaction,
            $quoteFactory,
            $cartManagement,
            $customerRepository,
            $emailManagement,
            $transactionManagement,
            $subscriptionConfig,
            $subscriptionManagement,
            $cartRepository,
            $logger,
            $recurringOrderRepository,
        );
    }

    /**
     * Check if the payment action is AUTH
     *
     * @param array<mixed> $transactionDetails
     * @return bool
     */
    public function isSchedulePaymentActionAuth(array $transactionDetails): bool
    {
        return isset($transactionDetails[Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE])
            && ($transactionDetails[Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE] === Constants::PAYMENT_TYPE_AUTH);
    }

    /**
     * Get Transaction Amount
     *
     * @param array<mixed> $transactionRequest
     * @return float|null
     */
    public function getTransactionAmount(array $transactionRequest): float|null
    {
        return $transactionRequest['amount'] ?? null;
    }

    /**
     * Check the transaction status
     *
     * @param array<mixed> $transactionDetails
     * @return string
     */
    public function checkTransactionStatus(array $transactionDetails): string
    {
        $resultCode = $this->getResultCode($transactionDetails);

        if ($resultCode) {
            $responseStatus = $this->utilities->validateResponse($resultCode);
            if ($responseStatus == Constants::SUCCESS
                || $responseStatus == Constants::PENDING
                || $responseStatus == Constants::MANUAL_REVIEW
            ) {
                return Constants::SUCCESS;
            }
        }
        return Constants::FAILED;
    }

    /**
     * Get Transaction ID from Response
     *
     * @param array<mixed> $transactionDetails
     * @return string
     */
    public function parseTransactionId(array $transactionDetails): string
    {
        return $transactionDetails['id'];
    }

    /**
     * Check if the transaction status is Manual Review
     *
     * @param array<mixed> $transactionDetails
     * @return false|int
     */
    public function isManualReview(array $transactionDetails): false|int
    {
        $resultCode = $this->getResultCode($transactionDetails);

        if ($resultCode) {
            return $this->utilities->isManualReviewResponse($resultCode);
        }
        return false;
    }

    /**
     * Get result code from response
     *
     * @param array<mixed> $response
     * @return string|null
     */
    private function getResultCode(array $response): string|null
    {
        return $response[Constants::KEY_NOTIFICATION_RESULT]
        [Constants::KEY_NOTIFICATION_CODE]
            ?? null;
    }

    /**
     * Get Reason for the failed transaction
     *
     * @param array<mixed> $scheduleResponse
     * @return string
     */
    public function getTransactionFailedReason(array $scheduleResponse): string
    {
        return $scheduleResponse[Constants::KEY_NOTIFICATION_RESULT]
        [self::KEY_RESULT_DESCRIPTION] ?? '';
    }

    /**
     * Process Failed Subscription Payment
     *
     * @param array<mixed> $transactionDetails
     * @param string $saleType
     * @param string $registrationId
     * @return void
     */
    public function processFailedSubscriptionPayment(
        array $transactionDetails,
        string $saleType,
        string $registrationId
    ): void {
        $this->aciTransactionManagement->manageFailedSubscriptionPayment(
            $transactionDetails,
            $saleType,
            $registrationId
        );
    }

    /**
     * Set payment method to Quote
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    public function setPaymentMethodToQuote(Quote $quote): void
    {
        $paymentMethod = AciGenericConfigProvider::CODE;
        $quote->getPayment()->importData(['method' => $paymentMethod]);
    }
}
