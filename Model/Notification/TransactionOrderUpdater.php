<?php
declare(strict_types=1);

namespace Aci\Payment\Model\Notification;

use Aci\Payment\Helper\Utilities;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use TryzensIgnite\Base\Model\Utilities\DataFormatter;
use TryzensIgnite\Notification\Helper\Constants as IgniteNotificationConstants;
use TryzensIgnite\Notification\Model\Utilities\Properties;
use Magento\Sales\Api\OrderRepositoryInterface;
use TryzensIgnite\Base\Model\Order\OrderManager;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Logger\Logger;
use TryzensIgnite\Notification\Model\TransactionOrderUpdater as IgniteTransactionOrderUpdaterModel;
use TryzensIgnite\Base\Api\OrderManagerInterface;
use Aci\Payment\Model\RecurringOrder;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;
use Aci\Payment\Model\Request\CreateSubscriptionRequestManager;
use Aci\Payment\Model\SubscriptionConfigProvider;

/**
 * class TransactionOrderUpdater - Update order based on notification
 */
class TransactionOrderUpdater extends IgniteTransactionOrderUpdaterModel
{

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var Properties
     */
    protected Properties $properties;

    /**
     * @var OrderManager
     */
    protected OrderManager $orderManager;

    /**
     * @var NotificationManager
     */
    private NotificationManager $notificationManager;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var NotificationUtilities
     */
    protected NotificationUtilities $notificationUtilities;

    /**
     * @var EventManager
     */
    private EventManager $eventsManager;

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;

    /**
     * @var OrderManagerInterface
     */
    private OrderManagerInterface $orderManagerInterface;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var CreateSubscriptionRequestManager
     */
    private CreateSubscriptionRequestManager $createSubscriptionRequestManager;

    /**
     * @var SubscriptionConfigProvider
     */
    private SubscriptionConfigProvider $subscriptionConfig;

    /**
     * TransactionOrderUpdater constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param Properties $properties
     * @param OrderManager $orderManager
     * @param NotificationManager $notificationManager
     * @param DataFormatter $dataFormatter
     * @param Logger $logger
     * @param NotificationUtilities $notificationUtilities
     * @param EventManager $eventsManager
     * @param Utilities $utilities
     * @param OrderFactory $orderFactory
     * @param OrderManagerInterface $orderManagerInterface
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param CreateSubscriptionRequestManager $createSubscriptionRequestManager
     * @param SubscriptionConfigProvider $subscriptionConfig
     */
    public function __construct(
        OrderRepositoryInterface                        $orderRepository,
        Properties                                      $properties,
        OrderManager                                    $orderManager,
        NotificationManager                             $notificationManager,
        DataFormatter                                   $dataFormatter,
        Logger                                          $logger,
        NotificationUtilities                           $notificationUtilities,
        EventManager                                    $eventsManager,
        Utilities                                       $utilities,
        OrderFactory                                    $orderFactory,
        OrderManagerInterface                           $orderManagerInterface,
        RecurringOrderRepositoryInterface               $recurringOrderRepository,
        CreateSubscriptionRequestManager                $createSubscriptionRequestManager,
        SubscriptionConfigProvider                      $subscriptionConfig
    ) {
        $this->notificationUtilities = $notificationUtilities;
        $this->eventsManager = $eventsManager;
        $this->orderManager            = $orderManager;
        $this->notificationManager     = $notificationManager;
        $this->logger                  = $logger;
        $this->utilities                 = $utilities;
        $this->orderFactory              = $orderFactory;
        $this->orderManagerInterface = $orderManagerInterface;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->createSubscriptionRequestManager = $createSubscriptionRequestManager;
        $this->subscriptionConfig = $subscriptionConfig;
        parent::__construct(
            $orderRepository,
            $properties,
            $orderManager,
            $notificationManager,
            $dataFormatter,
            $logger
        );
    }

    /**
     * Process Notification from cron
     *
     * @param array<mixed> $notificationContent
     * @return LocalizedException|string|null
     * @throws LocalizedException
     */
    public function processNotification(array $notificationContent): LocalizedException|string|null
    {
        // Check whether notification received is registration
        $isRegistrationResponse = $this->isRegistrationResponse($notificationContent);
        if ($isRegistrationResponse) {
            return IgniteNotificationConstants::NOTIFICATION_STATUS_SUCCESS;
        }
        // Check whether notification received is scheduler API response
        $isSchedulerResponse = $this->isSchedulerResponse($notificationContent);
        // Check whether notification received is transaction of recurring order
        $scheduleTransaction = $this->isRecurringOrderTransaction($notificationContent);
        if ($scheduleTransaction) {
            $this->eventsManager->dispatch(
                'tryzens_ignite_process_schedule_transaction',
                ['scheduleParams' => $notificationContent]
            );
            return IgniteNotificationConstants::NOTIFICATION_STATUS_SUCCESS;
        }
        if (!$isSchedulerResponse) {
            $action = $this->notificationManager->getActionType($notificationContent);
            $transactionId = $this->notificationManager->getTransactionId($notificationContent);
            $incrementId = $this->notificationManager->getIncrementIdFromNotification($notificationContent);
            // checks if the notification response is from schedule Creation order
            $isScheduledTransaction = $this->isScheduleCreationTransaction($notificationContent);
            if ($isScheduledTransaction) {
                $this->validateScheduleOrderCreation($notificationContent);
            }
            try {
                if ($incrementId) {
                    /** @var Order $order */
                    $order = $this->orderManager->getOrderByIncrementId($incrementId);
                    $isValidNotificationAmount = $this->validateNotificationAmount($notificationContent, $order);
                    if ($isValidNotificationAmount) {
                        return $this->processOrder($action, $notificationContent, $order, $transactionId);
                    }
                }
            } catch (\Exception) {
                $this->logger->info('Error occurred while processing notification');
                throw new LocalizedException(
                    __('Error occurred while processing notification')
                );
            }

        } else {
            $isSchedulerResponseProcessed = $this->processSchedulerNotification($notificationContent);
            if ($isSchedulerResponseProcessed) {
                return IgniteNotificationConstants::NOTIFICATION_STATUS_SUCCESS;
            }
        }
        return IgniteNotificationConstants::NOTIFICATION_STATUS_ERROR;
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
     * Get Transaction Amount from response.
     *
     * @param array<mixed> $notificationContent
     * @return float
     */
    public function getTransactionAmount(array $notificationContent): float
    {
        return (float)$this->notificationUtilities->getTransactionAmount($notificationContent);
    }

    /**
     * Validate notification response.
     *
     * @param array<mixed> $notificationContent
     * @return bool
     */
    public function isValidNotification(array $notificationContent): bool
    {
        $resultCode =
            $notificationContent[Constants::KEY_NOTIFICATION_PAYLOAD]
                [Constants::KEY_NOTIFICATION_RESULT]
                [Constants::KEY_NOTIFICATION_CODE]
                ?? null;
        $isSchedulerResponse = $this->isSchedulerResponse($notificationContent);
        $transactionId = $this->getTransactionId($notificationContent);
        $orderIncrementId = $this->getIncrementId($notificationContent);
        $isRecurringOrder = $this->isRecurringOrderTransaction($notificationContent);
        if (!$isSchedulerResponse &&
            !$isRecurringOrder &&
            (!$resultCode || !$transactionId || !$orderIncrementId)
        ) {
            $this->logger->error('One of the required value is blank. Order Id :' .
            $orderIncrementId. ', Transaction Id :' .
            $transactionId. ', Result Code :' .
            $resultCode);
            return false;
        }
        $responseStatus = $this->utilities->validateResponse($resultCode);

        if ($responseStatus === Constants::SUCCESS) {
            return true;
        } elseif ($responseStatus === Constants::PENDING) {
            return false;
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
                $action = $this->notificationManager->getActionType($notificationContent);
                if (in_array($orderState, $acceptableOrderStates) && in_array($action, $acceptablePaymentTypes)) {
                    $this->orderManagerInterface->cancelMagentoOrder(
                        (int)$order->getId(),
                        $this->getTransactionId($notificationContent)
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
            return false;
        }
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
        if ($this->subscriptionConfig->isSubscriptionActive()
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
     * If the webhook response belongs to schedule creation transaction
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function isScheduleCreationTransaction(array $paramsArray): bool
    {
        if ($this->subscriptionConfig->isSubscriptionActive()
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
                $recurringOrder->setRegistrationId($paramsArray['payload']['registrationId']);
                $recurringOrder->setLastOrderId((int)$orderId);
                $recurringOrder->setLastIncrementId($orderIncrementId);
                $this->recurringOrderRepository->save($recurringOrder);
                $this->createSubscriptionRequestManager->process($paramsArray['payload']);
            }
        }
        return true;
    }

    /**
     * Check if the api response is of Registration response type.
     *
     * @param array<mixed> $params
     * @return bool
     */
    public function isRegistrationResponse(array $params): bool
    {
        return $this->notificationUtilities->isRegistrationResponse($params);
    }
}
