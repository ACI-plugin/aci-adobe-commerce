<?php

namespace Aci\Payment\Model\Notification;

use Aci\Payment\Helper\Constants;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Sales\Model\OrderFactory;
use Aci\Payment\Model\Api\AciPaymentResponseManager;
use TryzensIgnite\Notification\Api\Data\NotificationInterfaceFactory;
use TryzensIgnite\Notification\Api\NotificationRepositoryInterface;
use TryzensIgnite\Notification\Model\Notification as NotificationModel;
use TryzensIgnite\Notification\Model\NotificationManager as IgniteNotificationManager;
use TryzensIgnite\Notification\Model\Utilities\Properties;
use Psr\Log\LoggerInterface;

class NotificationManager extends IgniteNotificationManager
{

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var NotificationUtilities
     */
    protected NotificationUtilities $notificationUtilities;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * NotificationManager constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param NotificationInterfaceFactory $notificationFactory
     * @param Serializer $serializer
     * @param Properties $properties
     * @param OrderFactory $orderFactory
     * @param AciPaymentResponseManager $responseManager
     * @param NotificationUtilities $notificationUtilities
     * @param LoggerInterface $logger
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        NotificationInterfaceFactory    $notificationFactory,
        Serializer                      $serializer,
        Properties                      $properties,
        OrderFactory                    $orderFactory,
        AciPaymentResponseManager       $responseManager,
        NotificationUtilities           $notificationUtilities,
        LoggerInterface                 $logger,
    ) {
        $this->notificationUtilities = $notificationUtilities;
        $this->serializer = $serializer;
        $this->logger = $logger;
        parent::__construct(
            $notificationRepository,
            $notificationFactory,
            $serializer,
            $properties,
            $orderFactory,
            $responseManager
        );
    }

    /**
     * Get Transaction ID from Response params.
     *
     * @param array<mixed> $notificationContent
     * @return mixed
     */
    public function getTransactionId(array $notificationContent): mixed
    {
        return $this->notificationUtilities->getTransactionId($notificationContent);
    }

    /**
     * Get action type from response params.
     *
     * @param array<mixed> $notificationContent
     * @return mixed
     */
    public function getActionType(array $notificationContent): mixed
    {
        return match ($this->notificationUtilities->getActionType($notificationContent)) {
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
     * @param array<mixed> $notificationContent
     * @return string|null
     */
    public function getIncrementIdFromNotification(array $notificationContent): ?string
    {
        return $this->notificationUtilities->getIncrementId($notificationContent);
    }

    /**
     * Check if the api response is of scheduler API call
     *
     * @param array<mixed> $notificationContent
     * @return bool
     */
    public function isSchedulerResponse(array $notificationContent): bool
    {
        return $this->notificationUtilities->isSchedulerResponse($notificationContent);
    }

    /**
     * Prepare data to be entered to notification table
     *
     * @param array<mixed> $notificationContent
     * @return array<mixed>
     */
    public function prepareNotificationData(array $notificationContent) : array
    {
        $transactionId ='';
        $incrementId = '';
        $isSchedulerResponse = $this->isSchedulerResponse($notificationContent);
        if (!$isSchedulerResponse) {
            try {
                $transactionId = $this->getTransactionId($notificationContent);
                $incrementId = $this->getIncrementIdFromNotification($notificationContent);
            } catch (\Exception $e) {
                $this->logger->error('Failed to get increment ID: ' . $e->getMessage());
                return [];
            }
        }
        return [
            'transaction_id' => $transactionId,
            'content' => $this->serializer->serialize($notificationContent),
            'order_increment_id' => $incrementId,
            'order_id' => null,
            'status' => NotificationModel::NOTIFICATION_STATUS_PENDING,
            'retry_count' => 0
        ];
    }
}
