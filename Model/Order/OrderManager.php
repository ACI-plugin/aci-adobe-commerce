<?php
declare(strict_types=1);

namespace Aci\Payment\Model\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use TryzensIgnite\Common\Model\Order\OrderManager as IgniteCommonOrderManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Common\Helper\Constants as IgniteConstants;

/**
 * Order management for payment methods
 */
class OrderManager extends IgniteCommonOrderManager
{
    /**
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
     */
    public function __construct(
        OrderManagementInterface        $orderManagement,
        OrderStatusHistoryFactory       $orderStatusHistoryFactory,
        OrderRepositoryInterface        $orderRepository,
        TransactionRepositoryInterface  $transactionRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        BuilderInterface                $paymentTransactionBuilder,
        OrderFactory                    $orderFactory,
        CheckoutSession                 $checkoutSession,
        InvoiceService                  $invoiceService,
        InvoiceSender                   $invoiceSender,
        Transaction                     $transaction,
    ) {

        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
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
            $transaction
        );
    }

    /**
     * Check if order is valid
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isValidOrder(array $response): bool
    {
        $apiTransactionAmount = $this->getTransactionAmountFromResponse($response);
        $incrementId = $this->getOrderIncrementIdFromResponse($response);
        /** @var Order $order */
        $order = $this->getOrderByIncrementId($incrementId);
        $orderTotalAmount = $order->getGrandTotal();
        return $apiTransactionAmount == $orderTotalAmount;
    }

    /**
     * Process order
     *
     * @param array<mixed> $response
     * @param bool $isAuthOnly
     * @param bool $isCaptured
     * @param bool $manualReview
     * @return bool|int
     * @throws LocalizedException
     */
    public function processOrder(
        array $response,
        bool $isAuthOnly = false,
        bool $isCaptured = false,
        bool $manualReview = false
    ): bool|int {
        $orderIncrementId = $this->getOrderIncrementIdFromResponse($response);
        if (!$orderIncrementId) {
            return false;
        }
        $checkoutTransactionId = $this->getCheckoutIdFromResponse($response);
        $transactionAmount = $this->getTransactionAmountFromResponse($response);
        $paymentBrand = $this->getPaymentBrandFromResponse($response);
        /** @var Order $order */
        $order = $this->getOrderByIncrementId($orderIncrementId);
        $orderId = (int)$order->getEntityId();
        if (!$orderId) {
            return false;
        }
        //Update payment information
        $this->updateOrderPayment($order, $response);
        if ($manualReview) { //If the transaction is to be reviewed manually
            $state = self::ORDER_STATE_PAYMENT_REVIEW;
            $status = self::ORDER_STATUS_PAYMENT_REVIEW;

            $this->updateOrderStatus(
                sprintf(
                    'Payment is to be reviewed manually. CheckoutId: %s Authorized amount: %s Payment Brand: %s',
                    $checkoutTransactionId,
                    $transactionAmount,
                    $paymentBrand
                ),
                $state,
                $status,
                $orderId
            );
            return $orderId;
        } elseif ($isAuthOnly) {
            //If auth only transaction
            $state = self::ORDER_STATE_PENDING;
            $status = self::ORDER_STATUS_PENDING;
            //Add transaction entry
            $this->addNewTransactionEntry(
                $order,
                $checkoutTransactionId,
                Constants::SERVICE_AUTHORIZATION
            );
            $this->updateOrderStatus(
                sprintf(
                    'Payment authorized. CheckoutId: %s Authorized amount: %s Payment Brand: %s',
                    $checkoutTransactionId,
                    $transactionAmount,
                    $paymentBrand
                ),
                $state,
                $status,
                $orderId
            );
            return $orderId;
        } elseif ($isCaptured) {
            $state = self::ORDER_STATE_PROCESSING;
            $status = self::ORDER_STATUS_PROCESSING;
            //Generate Invoice
            $this->generateInvoice($order, $checkoutTransactionId);
            //Add order comment
            $this->updateOrderStatus(
                sprintf(
                    'Payment captured. CheckoutId: %s Captured amount: %s Payment Brand: %s',
                    $checkoutTransactionId,
                    $transactionAmount,
                    $paymentBrand
                ),
                $state,
                $status,
                $orderId
            );
            return $orderId;
        }
        return false;
    }

    /**
     * Update order payment information
     *
     * @param Order $order
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function updateOrderPayment(Order $order, array $response): void
    {
        $transactionId = $this->getCheckoutIdFromResponse($response);

        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(Constants::GET_TRANSACTION_RESPONSE, $response);
        $payment->setIsTransactionClosed(false);
        $payment->setParentTransactionId($transactionId);

        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }

    /**
     * Get CheckoutId from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getCheckoutIdFromResponse(array $response): mixed
    {
        return $response[Constants::KEY_CHECKOUT_ID] ??
            $response[IgniteConstants::CHECKOUT_TRANSACTION_ID];
    }

    /**
     * Get Order IncrementId from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getOrderIncrementIdFromResponse(array $response): mixed
    {
        return $response[Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID] ??
            $response[IgniteConstants::INVOICE_NUMBER];
    }

    /**
     * Get Transaction Amount from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionAmountFromResponse(array $response): mixed
    {
        return $response[Constants::KEY_ACI_PAYMENT_AMOUNT] ??
            $response[IgniteConstants::GET_TRANSACTION_REQUEST][IgniteConstants::TRANSACTION_TOTAL];
    }

    /**
     * Get payment brand from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getPaymentBrandFromResponse(array $response): mixed
    {
        return $response[Constants::KEY_PAYMENT_BRAND] ?? '';
    }
}
