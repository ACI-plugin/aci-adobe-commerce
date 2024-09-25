<?php
declare(strict_types=1);

namespace Aci\Payment\Model\Notification;

use Aci\Payment\Helper\Constants;
use Aci\Payment\Model\Request\CreateSubscriptionRequestManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use TryzensIgnite\Notification\Helper\Constants as IgniteConstants;
use TryzensIgnite\Notification\Model\TransactionOrderUpdater as IgniteTransactionOrderUpdaterModel;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Invoice;
use TryzensIgnite\Common\Api\OrderManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aci\Payment\Logger\AciLogger;
use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Notification\Helper\Constants as IgniteNotificationConstants;
use Magento\Sales\Model\Order;
use TryzensIgnite\Subscription\Api\RecurringOrderRepositoryInterface;
use TryzensIgnite\Subscription\Model\RecurringOrder;
use TryzensIgnite\Common\Model\Order\OrderManager as OrderManager;

/**
 * class TransactionOrderUpdater - Update order based on notification
 */
class TransactionOrderUpdater extends IgniteTransactionOrderUpdaterModel
{

    /**
     * @var NotificationUtilities
     */
    protected NotificationUtilities $notificationUtilities;

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var CreateSubscriptionRequestManager
     */
    private CreateSubscriptionRequestManager $createSubscriptionRequestManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * TransactionOrderUpdater constructor
     *
     * @param OrderManagerInterface $orderManagerInterface
     * @param CreditmemoManagementInterface $creditmemoManagement
     * @param CreditmemoLoader $creditmemoLoader
     * @param Invoice $invoice
     * @param OrderRepositoryInterface $orderRepository
     * @param AciLogger $logger
     * @param NotificationUtilities $notificationUtilities
     * @param Utilities $utilities
     * @param OrderFactory $orderFactory
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param CreateSubscriptionRequestManager $createSubscriptionRequestManager
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderManager $orderManager
     * @param EventManager $eventsManager
     */
    public function __construct(
        OrderManagerInterface         $orderManagerInterface,
        CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoLoader              $creditmemoLoader,
        Invoice                       $invoice,
        OrderRepositoryInterface      $orderRepository,
        AciLogger                     $logger,
        NotificationUtilities         $notificationUtilities,
        Utilities                     $utilities,
        OrderFactory                    $orderFactory,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        CreateSubscriptionRequestManager $createSubscriptionRequestManager,
        ScopeConfigInterface            $scopeConfig,
        OrderManager                  $orderManager,
        EventManager                    $eventsManager
    ) {
        $this->notificationUtilities    = $notificationUtilities;
        $this->utilities                 = $utilities;
        $this->orderFactory              = $orderFactory;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->createSubscriptionRequestManager = $createSubscriptionRequestManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $orderManagerInterface,
            $creditmemoManagement,
            $creditmemoLoader,
            $invoice,
            $orderRepository,
            $logger,
            $orderManager,
            $eventsManager
        );
    }

    /**
     * Get Transaction ID from Response params.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionId(array $response): mixed
    {
        return $this->notificationUtilities->getTransactionId($response);
    }

    /**
     * Get action type from response params.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getActionType(array $response): mixed
    {
        return match ($this->notificationUtilities->getActionType($response)) {
            Constants::PAYMENT_TYPE_CAPTURE, Constants::PAYMENT_TYPE_SALE => Constants::NOTIFICATION_SERVICE_CAPTURE,
            Constants::PAYMENT_TYPE_REFUND => Constants::SERVICE_REFUND,
            Constants::PAYMENT_TYPE_CANCEL => Constants::SERVICE_VOID,
            Constants::PAYMENT_TYPE_AUTH => Constants::SERVICE_AUTHORIZE,
            default => ''
        };
    }

    /**
     * Get increment id from response params.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getIncrementId(array $response): mixed
    {
        return $this->notificationUtilities->getIncrementId($response);
    }

    /**
     * Format TransactionDetails array from response array
     *
     * @param array<mixed> $response
     * @return array<mixed>
     */
    public function getTransactionDetails(array $response): array
    {
        $orderId = $this->getIncrementId($response);
        $transactionId = $this->getTransactionId($response);
        $transactionAmt = $this->notificationUtilities->getTransactionAmount($response);
        $paymentBrand = $this->notificationUtilities->getPaymentBrand($response);
        if (!$orderId || !$transactionId || !$transactionAmt) {
            $this->logger->error(__('One of the required value is blank. Order Id :' .
                $orderId. ', Transaction Id :' .
                $transactionId. ', TransactionAmount :' .
                $transactionAmt));
            return [];
        }
        return [
            'ResponseMessage' => '',
            'CheckoutTransactionId' => $transactionId,
            'InvoiceNumber' => $orderId,
            'TransactionId' => $transactionId,
            'OriginalRequest' => [
                'InvoiceNumber' => $orderId,
                'TransactionTotal' => $transactionAmt,
                'CaptureMode' => 0
            ],
            'AuthResponse' => [
                'Amount' => $transactionAmt,
                'Decision' => 'ACCEPT'
            ],
            'paymentBrand' => $paymentBrand
        ];
    }

    /**
     * Get Transaction Amount from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionAmount(array $response): mixed
    {
        return $this->notificationUtilities->getTransactionAmount($response);
    }

    /**
     * Validate notification response.
     *
     * @param array<mixed> $response
     * @param bool $isSchedulerNotification
     * @return bool|string
     */
    public function validateNotificationResponse(array $response, $isSchedulerNotification = false): bool | string
    {
        $resultCode =
            $response[Constants::KEY_NOTIFICATION_PAYLOAD]
                [Constants::KEY_NOTIFICATION_RESULT]
                [Constants::KEY_NOTIFICATION_CODE]
                ?? null;
        $transactionId = $this->getTransactionId($response);
        $orderIncrementId = $this->getIncrementId($response);
        if (!$isSchedulerNotification && (!$resultCode || !$transactionId || !$orderIncrementId)) {
            $this->logger->error('One of the required value is blank. Order Id :' .
            $orderIncrementId. ', Transaction Id :' .
            $transactionId. ', Result Code :' .
            $resultCode);
            return IgniteNotificationConstants::KEY_NOTIFICATION_INVALID;
        }
        $responseStatus = $this->utilities->validateResponse($resultCode);

        if ($responseStatus === Constants::SUCCESS) {
            return true;
        } elseif ($responseStatus === Constants::PENDING) {
            return IgniteNotificationConstants::KEY_NOTIFICATION_INVALID;
        } else {
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            if ($order->getId()) {
                $orderState = $order->getState();
                $acceptableOrderStates = [
                    Order::STATE_PENDING_PAYMENT,
                    Order::STATE_PAYMENT_REVIEW
                ];
                $acceptablePaymentTypes = [
                    Constants::NOTIFICATION_SERVICE_CAPTURE,
                    Constants::SERVICE_AUTHORIZE
                ];
                $action = $this->getActionType($response);
                if (in_array($orderState, $acceptableOrderStates) && in_array($action, $acceptablePaymentTypes)) {
                    $this->orderManagerInterface->cancelMagentoOrder(
                        (int)$order->getId(),
                        $this->getTransactionId($response)
                    );
                } else {
                    $this->logger->error('Webhook api result code error - Order not in the correct state - '.
                        $orderIncrementId.
                     ' Current Order State -'. $orderState);
                }
            } else {
                $this->logger->error('Webhook api result code error - Order Id not found
                for the increment Id - '.$orderIncrementId);
            }
            return IgniteNotificationConstants::KEY_NOTIFICATION_INVALID;
        }
    }

    /**
     * If the webhook response belongs to schedule creation transaction
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function isScheduleCreationTransaction(array $paramsArray): bool
    {
        if ($this->scopeConfig->getValue(IgniteNotificationConstants::KEY_SUBSCRIPTION_ENABLED)
            && isset($paramsArray['payload']['standingInstruction']['recurringType'])
        ) {
            if ($paramsArray['payload']['standingInstruction']['recurringType'] ==
                Constants::STANDING_INSTRUCTION_RECURRING_TYPE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the Schedule order creation has been done
     *
     * @param array<mixed> $paramsArray
     * @return bool
     * @throws LocalizedException
     */
    public function validateScheduleOrderCreation(array $paramsArray): bool
    {

        $orderIncrementId = null;
        if (isset($paramsArray['payload']['merchantTransactionId'])) {
            $orderIncrementId = $paramsArray['payload']['merchantTransactionId'];
        }
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        $orderId = $order->getId();
        if ($orderId) {
            try {
                $recurringOrder = $this->recurringOrderRepository->getByOrderId((int)$orderId);
            } catch (LocalizedException $e) {
                $this->logger->error(__($e->getMessage()));
                return false;
            }
            if (!$recurringOrder->getRegistrationId()) {
                //checking if registration ID is null. If null that would mean the response of schedule order was
                // not received. we need to make the api call again from data we populated in ignite_subscription
                // table using app/code/TryzensIgnite/Subscription/Plugin/OrderPlaceAfter.php
                $recurringOrder->setRegistrationId($paramsArray['payload']['registrationId']);
                $this->recurringOrderRepository->save($recurringOrder);
                $this->createSubscriptionRequestManager->process($paramsArray);
            }
        }
        return true;
    }

    /**
     * Check scheduler table to see if the notification needs to be processed
     *
     * @param array<mixed> $paramsArray
     * @return bool
     * @throws LocalizedException
     */
    public function processSchedulerNotification(array $paramsArray): bool
    {
        $registrationId = null;
        if (isset($paramsArray[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_REGISTRATION_ID])) {
            $registrationId = $paramsArray[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_REGISTRATION_ID];
        }
        if ($registrationId) {
            try {
                $recurringOrder = $this->recurringOrderRepository->getByRegistrationId($registrationId);
            } catch (LocalizedException $e) {
                $this->logger->error(__($e->getMessage()));
                return false;
            }
            if (!$recurringOrder->getSubscriptionId()) {
                $recurringOrder->setSubscriptionId($paramsArray[Constants::KEY_NOTIFICATION_PAYLOAD]['id']);
                $recurringOrder->setStatus(RecurringOrder::SUB_STATUS_ACTIVE);
                $this->recurringOrderRepository->save($recurringOrder);
            }
        }
        return true;
    }

    /**
     * Check if the api response is of scheduler API call
     *
     * @param array<mixed> $params
     * @return bool
     */
    public function isSchedulerResponse(array $params): bool
    {
        return $this->notificationUtilities->isSchedulerResponse($params);
    }

    /**
     * If the webhook response belongs to schedule transaction
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function isRecurringOrderTransaction(array $paramsArray): bool
    {
        if ($this->scopeConfig->getValue(IgniteConstants::KEY_SUBSCRIPTION_ENABLED)
            && $this->getRecurringParam($paramsArray)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check recurring transaction
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function getRecurringParam(array $paramsArray): bool
    {
        if (isset($paramsArray[Constants::KEY_NOTIFICATION_TYPE]) &&
            $paramsArray[Constants::KEY_NOTIFICATION_TYPE] == Constants::KEY_NOTIFICATION_TYPE_PAYMENT &&
            isset($paramsArray[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_NOTIFICATION_SOURCE]) &&
            $paramsArray[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_NOTIFICATION_SOURCE]
            == Constants::KEY_NOTIFICATION_SOURCE_SCHEDULER
        ) {
            return true;
        }
        return false;
    }
}
