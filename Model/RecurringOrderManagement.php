<?php

namespace Aci\Payment\Model;

use Aci\Payment\Helper\Constants;
use Aci\Payment\Model\Ui\AciGenericConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Aci\Payment\Model\Subscription\Config as SubscriptionModelConfig;
use TryzensIgnite\Base\Model\Order\OrderManager as IgniteBaseOrderManager;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\Transaction;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\CartRepositoryInterface;
use Aci\Payment\Model\Subscription\Logger\Logger;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;
use TryzensIgnite\Base\Model\Utilities\DataFormatter;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use TryzensIgnite\Base\Model\Utilities\Config as UtilityConfig;
use Aci\Payment\Helper\Utilities;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Manage order process
 */
class RecurringOrderManagement extends IgniteBaseOrderManager
{
    public const ORDER_STATUS_PENDING           =   'pending';
    public const ORDER_STATE_PENDING            =   'pending';
    public const AMOUNT_MISMATCH                =   'Transaction amount is different from Order total';
    public const ACTION_SALE                    =   'Sale';
    public const KEY_MANUAL_REVIEW              =   'ManualReview';
    public const KEY_PAYMENT_BRAND              =   'paymentBrand';
    public const KEY_TRANSACTION_ID             =   'TransactionId';

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $quoteFactory;

    /**
     * @var CartManagementInterface
     */
    protected CartManagementInterface $cartManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var EmailManagement
     */
    protected EmailManagement $emailManagement;

    /**
     * @var RecurringOrderTransactionManagement
     */
    protected RecurringOrderTransactionManagement $recurringOrderTransactionManagement;

    /**
     * @var UtilityConfig
     */
    protected UtilityConfig $utilityConfig;

    /**
     * @var SubscriptionManagement
     */
    protected SubscriptionManagement $subscriptionManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

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
     * @param QuoteFactory $quoteFactory
     * @param CartManagementInterface $cartManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailManagement $emailManagement
     * @param RecurringOrderTransactionManagement $recurringOrderTransactionManagement
     * @param UtilityConfig $utilityConfig
     * @param SubscriptionManagement $subscriptionManagement
     * @param CartRepositoryInterface $cartRepository
     * @param Logger $logger
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param DataFormatter $dataFormatter
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param CreditmemoLoader $creditmemoLoader
     * @param Invoice $invoice
     * @param CreditmemoSender $creditmemoSender
     * @param Utilities $utilities
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
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
        RecurringOrderTransactionManagement $recurringOrderTransactionManagement,
        UtilityConfig $utilityConfig,
        SubscriptionManagement $subscriptionManagement,
        CartRepositoryInterface $cartRepository,
        Logger $logger,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        DataFormatter                   $dataFormatter,
        CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoLoader              $creditmemoLoader,
        Invoice                       $invoice,
        CreditmemoSender              $creditmemoSender,
        Utilities $utilities,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->cartManagement = $cartManagement;
        $this->customerRepository = $customerRepository;
        $this->emailManagement = $emailManagement;
        $this->recurringOrderTransactionManagement = $recurringOrderTransactionManagement;
        $this->utilityConfig = $utilityConfig;
        $this->subscriptionManagement = $subscriptionManagement;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->utilities = $utilities;
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
     * Manage Order
     *
     * @param array<mixed> $scheduleResponse
     * @param array<mixed> $transactionDetails
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws MailException
     */
    public function createRecurringOrder(array $scheduleResponse, array $transactionDetails): mixed
    {
        $registrationId = $this->parseRegistrationId($scheduleResponse);
        $orderId = $this->subscriptionManagement->getOrderIdFromRegistrationId($registrationId);
        $subscriptionId = null;
        if ($orderId) {
            $subscriptionId = $this->getSubscriptionId($orderId);
            if (!$subscriptionId) {
                return null;
            }
        }
        $isAuthOnly = $this->isSchedulePaymentActionAuth($transactionDetails);
        $transactionAmount = $this->getTransactionAmount($transactionDetails);
        $transactionStatus = $this->checkTransactionStatus($transactionDetails);
        $transactionId = $this->parseTransactionId($transactionDetails);
        $manualReview = $this->isManualReview($transactionDetails);
        /** @var Order $order */
        $order = $this->orderRepository->get((int)$orderId);
        $store = $order->getStore();
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $order->getPayment();
        $paymentBrand = $this->getPaymentBrand($transactionDetails, $orderPayment);
        $customerId = (int)$order->getCustomerId();
        $customer = $this->customerRepository->getById($customerId);

        try {
            //Create the empty quote object
            $quote = $this->quoteFactory->create();
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->assignCustomer($customer);
            $orderItems = $order->getAllVisibleItems();
            $shippingAmount =0;
            if ($order->getShippingAmount()) {
                $shippingAmount = $order->getShippingAmount();
            }
            foreach ($orderItems as $item) {
                /** @var Product $product */
                $product = $item->getProduct();
                $quote->addProduct($product, $item->getBuyRequest());
            }
            /** @var Address $billingAddress */
            $billingAddress = $order->getBillingAddress();
            $quote->getBillingAddress()->addData($billingAddress->getData());
            if (!$order->getIsVirtual()) {
                /** @var Address $shippingAddressObject */
                $shippingAddressObject = $order->getShippingAddress();
                $shippingAddress = $quote->getShippingAddress()->addData($shippingAddressObject->getData());
                if ($order->getShippingMethod() && is_string($order->getShippingMethod())) {
                    $shippingAddress->setShippingMethod($order->getShippingMethod());
                }
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->setShippingAmount($shippingAmount);
                $shippingAddress->setBaseShippingAmount($shippingAmount);
                $quote->setTotalsCollectedFlag(false);/** @phpstan-ignore-line */
                $quote->collectTotals();
            }
            $this->cartRepository->save($quote);
            $this->setPaymentMethodToQuote($quote);
            $quoteTotal = $quote->getGrandTotal();
            if ($quoteTotal !== $transactionAmount) {
                $reason = self::AMOUNT_MISMATCH;
                $this->handleFailedSubscription($transactionDetails, $registrationId, $order, __($reason));
                $quote->setIsActive(false);
                $this->cartRepository->save($quote);
                return null;
            }

            /** @var Order $newOrder */
            $newOrder = $this->cartManagement->submit($quote); /** @phpstan-ignore-line */
            $newOrderId = $newOrder->getId();
            $newIncrementId = $newOrder->getIncrementId();
            $oldPaymentMethod = $orderPayment->getMethod();
            $this->setPaymentMethodFromParentOrder(
                $newOrder,
                $transactionId,
                $transactionDetails,
                $oldPaymentMethod
            );
            if ($transactionStatus === Constants::FAILED) {
                $this->cancelMagentoOrder(
                    $newOrderId,
                    $transactionId
                );
                $transactionMessage = $this->getTransactionFailedReason($scheduleResponse);
                $this->handleFailedSubscription(
                    $transactionDetails,
                    $registrationId,
                    $order,
                    $transactionMessage,
                    false
                );
                return null;
            }
            if ($manualReview) { //If the transaction is to be reviewed manually
                $state = Order::STATE_PAYMENT_REVIEW;
                $status = Order::STATE_PAYMENT_REVIEW;
                $this->updateOrderStatus(
                    sprintf(
                        'Payment has to be reviewed manually. TransactionId: %s, Authorized amount: %s,
                        Payment Brand: %s, SubscriptionID : %s',
                        $transactionId,
                        $transactionAmount,
                        $paymentBrand,
                        $subscriptionId
                    ),
                    $state,
                    $status,
                    $newOrderId
                );

            } elseif ($isAuthOnly) {
                //If auth only transaction
                $state = self::ORDER_STATE_PENDING;
                $status = self::ORDER_STATUS_PENDING;
                //Add transaction entry
                $this->addNewTransactionEntry(
                    $newOrder,
                    $transactionId,
                    Constants::SERVICE_AUTHORIZATION
                );
                $this->updateOrderStatus(
                    sprintf(
                        'Payment authorized. TransactionId: %s, Authorized amount: %s, Payment Brand: %s,
                        SubscriptionID : %s',
                        $transactionId,
                        $transactionAmount,
                        $paymentBrand,
                        $subscriptionId
                    ),
                    $state,
                    $status,
                    $newOrderId
                );
            } else {
                $state = Order::STATE_PROCESSING;
                $status = Order::STATE_PROCESSING;
                //Generate Invoice
                $this->generateInvoice($newOrder, $transactionId);
                //Add order comment
                $this->updateOrderStatus(
                    sprintf(
                        'Payment captured. TransactionId: %s, Captured amount: %s, SubscriptionID : %s',
                        $transactionId,
                        $transactionAmount,
                        $subscriptionId
                    ),
                    $state,
                    $status,
                    $newOrderId
                );
            }
            $this->updateSubscriptionTable($newOrderId, $registrationId, $newIncrementId);
            return $newOrderId;
        } catch (NoSuchEntityException|LocalizedException|\Exception $e) {
            $this->handleFailedSubscription($transactionDetails, $registrationId, $order, $e->getMessage());
        }
        return null;
    }

    /**
     * Manage Order Payment
     *
     * @param Order $order
     * @param string $transactionId
     * @param array<mixed> $response
     * @param string $oldPaymentMethod
     * @return void
     * @throws LocalizedException
     */
    private function setPaymentMethodFromParentOrder(
        order $order,
        string $transactionId,
        array $response,
        string $oldPaymentMethod
    ): void {
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setMethod($oldPaymentMethod);
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(Constants::TRANSACTION_ID, $transactionId);
        $payment->setAdditionalInformation(Constants::GET_TRANSACTION_RESPONSE, $response);
        $payment->setIsTransactionClosed(false);
        $payment->setParentTransactionId($transactionId);

        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }

    /**
     * Update Database table
     *
     * @param int $orderId
     * @param string $registrationId
     * @param string $newIncrementId
     * @return void
     * @throws LocalizedException
     */
    public function updateSubscriptionTable(int $orderId, string $registrationId, string $newIncrementId): void
    {
        $this->subscriptionManagement->updateLastOrderId($orderId, $registrationId, $newIncrementId);
        $this->subscriptionManagement->insertSubscriptionOrder($orderId, $registrationId);
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
     * Get Registration Id
     *
     * @param array<mixed> $scheduleResponse
     * @return string
     */
    public function parseRegistrationId(array $scheduleResponse): string
    {
        return $scheduleResponse['payload']['registrationId'];
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
     * Get payment brand from response.
     *
     * @param array<mixed> $response
     * @param OrderPaymentInterface $orderPayment
     * @return mixed
     */
    public function getPaymentBrand(array $response, OrderPaymentInterface $orderPayment): mixed
    {
        return $response[self::KEY_PAYMENT_BRAND] ?? $orderPayment->getMethod();
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
     * Process Cancel/Refund operation
     *
     * @param array<mixed> $transactionDetails
     * @param string $registrationId
     * @return void
     * @throws LocalizedException
     */
    public function manageFailedSubscriptionPayment(array $transactionDetails, string $registrationId): void
    {
        $saleType = $this->isSchedulePaymentActionAuth($transactionDetails)
            ? SubscriptionConstants::ACTION_AUTH : self::ACTION_SALE;
        $this->processFailedSubscriptionPayment($transactionDetails, $saleType, $registrationId);
    }

    /**
     * Handle Failed Subscription
     *
     * @param array<mixed> $transactionDetails
     * @param string $registrationId
     * @param Order $order
     * @param string $reason
     * @param bool $paymentOperation
     * @return void
     * @throws MailException
     * @throws LocalizedException
     */
    public function handleFailedSubscription(
        array $transactionDetails,
        string $registrationId,
        Order $order,
        string $reason,
        bool $paymentOperation = true
    ): void {

        $salesSupportEmail = $this->utilityConfig->getConfig(SubscriptionModelConfig::KEY_SALES_SUPPORT_EMAIL);
        $salesSupportName = $this->utilityConfig->getConfig(SubscriptionModelConfig::KEY_SALES_SUPPORT_NAME);

        if ($paymentOperation) {
            $this->manageFailedSubscriptionPayment($transactionDetails, $registrationId);
        }

        $this->emailManagement->sendMessage(
            $registrationId,
            $salesSupportEmail,
            $salesSupportName,
            $order->getCustomerEmail(),
            $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
            'fail',
            $reason
        );
        $this->logger->info('Subscription Failed::' . $reason);
    }

    /**
     * Get Reason for the failed transaction
     *
     * @param array<mixed> $scheduleResponse
     * @return string
     */
    public function getTransactionFailedReason(array $scheduleResponse): string
    {
        return $scheduleResponse['payload']['result']['description'] ?? '';
    }

    /**
     * Process Failed Subscription Payment
     *
     * @param array<mixed> $transactionDetails
     * @param string $saleType
     * @param string $registrationId
     * @return void
     * @throws LocalizedException
     */
    public function processFailedSubscriptionPayment(
        array $transactionDetails,
        string $saleType,
        string $registrationId
    ): void {
        $this->recurringOrderTransactionManagement
            ->manageFailedSubscriptionPayment($transactionDetails, $saleType, $registrationId);
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

    /**
     * Method to get subscription ID of the recurring order
     *
     * @param int $orderId
     * @return string|null
     */
    public function getSubscriptionId(int $orderId) :?string
    {
        try {
            $subscription = $this->recurringOrderRepository->getByOrderId($orderId);
            return $subscription->getSubscriptionId();
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
