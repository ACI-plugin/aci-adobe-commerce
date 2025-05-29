<?php
declare(strict_types=1);

namespace Aci\Payment\Model\Order;

use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\OrderFactory;
use TryzensIgnite\Base\Model\Utilities\DataFormatter;
use TryzensIgnite\Base\Model\Order\OrderManager as BaseIgniteOrderManager;
use Aci\Payment\Model\Notification\NotificationUtilities;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Aci\Payment\Helper\Constants;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Order management for payment methods
 */

class OrderManager extends BaseIgniteOrderManager
{
    /**
     * @var NotificationUtilities
     */
    protected NotificationUtilities $notificationUtilities;

    /**
     * @var string|null
     */
    protected ?string $orderIncrementId = null;

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
     * @param DataFormatter $dataFormatter
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param CreditmemoLoader $creditmemoLoader
     * @param Invoice $invoice
     * @param CreditmemoSender $creditmemoSender
     * @param NotificationUtilities $notificationUtilities
     * @param OrderItemRepositoryInterface $orderItemRepository
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
        DataFormatter                   $dataFormatter,
        CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoLoader              $creditmemoLoader,
        Invoice                       $invoice,
        CreditmemoSender              $creditmemoSender,
        NotificationUtilities            $notificationUtilities,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->notificationUtilities = $notificationUtilities;
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
            $transaction,
            $dataFormatter,
            $creditmemoManagement,
            $creditmemoLoader,
            $invoice,
            $creditmemoSender,
            $orderItemRepository
        );
    }

    /**
     * Process order
     *
     * @param array<mixed> $response
     * @param string $paymentMethodType
     * @param bool $isAuthOnly
     * @param bool $isCaptured
     * @param bool $manualReview
     * @return bool|int
     * @throws LocalizedException
     */
    public function processOrder(
        array $response,
        string $paymentMethodType = '',
        bool $isAuthOnly = false,
        bool $isCaptured = false,
        bool $manualReview = false
    ): bool|int {
        $processedOrderId = parent::processOrder($response, $paymentMethodType, $isAuthOnly, $isCaptured);
        if ($processedOrderId) {
            return $processedOrderId;
        } elseif ($manualReview) { //If the transaction is to be reviewed manually
            /** @var Order $order */
            $order = $this->getOrderToProcess($response);
            $orderId = (int)$order->getEntityId();
            if (!$orderId) {
                return false;
            }
            $state = self::ORDER_STATE_PAYMENT_REVIEW;
            $status = self::ORDER_STATUS_PAYMENT_REVIEW;
            $checkoutTransactionId = $this->getTransactionId($response);
            $transactionAmount = $this->getTransactionAmountFromResponse($response);
            $this->updateOrderPayment($order, $response);
            $this->updateOrderStatus(
                sprintf(
                    'Payment is to be reviewed manually. TransactionId: %s Authorized amount: %s Payment Method: %s',
                    $checkoutTransactionId,
                    $transactionAmount,
                    $paymentMethodType
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
        $transactionId = $this->getTransactionId($response);

        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(Constants::GET_TRANSACTION_RESPONSE, $response);
        $payment->setIsTransactionClosed(false);
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }

    /**
     * Get Order IncrementId from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getOrderIncrementIdFromResponse(array $response): mixed
    {
        if ($this->orderIncrementId == null) {
            $this->orderIncrementId = $response[Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID] ??
                $response[Constants::INVOICE_NUMBER];
        }
        return $this->orderIncrementId;
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
            $response[Constants::GET_TRANSACTION_REQUEST][Constants::TRANSACTION_TOTAL];
    }

    /**
     * Get order total from API response
     *
     * @param array<mixed> $response
     * @return float|int
     */
    public function getApiResponseOrderTotal(array $response): float|int
    {
        $transactionAmountFromNotification = $this->notificationUtilities->getTransactionAmount($response);
        if (isset($transactionAmountFromNotification)) {
            return (float)$transactionAmountFromNotification;
        }
        return (float)$this->getTransactionAmountFromResponse($response);
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

    /**
     * Get Transaction ID from Response params.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionId(array $response): mixed
    {
        return $response[Constants::KEY_CHECKOUT_ID] ??
            $response[Constants::CHECKOUT_TRANSACTION_ID];
    }

    /**
     * Get order to process
     *
     * @param array<mixed> $response
     * @return OrderInterface|null
     */
    public function getOrderToProcess(array $response = []): ?OrderInterface
    {
        $orderIncrementId = $this->orderIncrementId ??
            $this->getOrderIncrementIdFromResponse($response);
        return $this->getOrderByIncrementId($orderIncrementId);
    }
}
