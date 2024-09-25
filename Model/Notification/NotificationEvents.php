<?php

namespace Aci\Payment\Model\Notification;

use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Psr\Log\LoggerInterface;
use TryzensIgnite\Notification\Api\Data\NotificationInterfaceFactory;
use TryzensIgnite\Notification\Api\NotificationRepositoryInterface;
use TryzensIgnite\Notification\Model\NotificationEvents as IgniteNotificationEvents;
use TryzensIgnite\Notification\Model\TransactionOrderUpdater;

class NotificationEvents extends IgniteNotificationEvents
{
    /**
     * @var NotificationUtilities
     */
    protected NotificationUtilities $notificationUtilities;

    /**
     * @param TransactionOrderUpdater $transactionOrderUpdater
     * @param NotificationRepositoryInterface $notificationRepository
     * @param NotificationInterfaceFactory $notificationFactory
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param NotificationUtilities $notificationUtilities
     */
    public function __construct(
        TransactionOrderUpdater         $transactionOrderUpdater,
        NotificationRepositoryInterface $notificationRepository,
        NotificationInterfaceFactory    $notificationFactory,
        LoggerInterface                 $logger,
        Serializer                      $serializer,
        NotificationUtilities           $notificationUtilities
    ) {
        $this->notificationUtilities = $notificationUtilities;
        parent::__construct(
            $transactionOrderUpdater,
            $notificationRepository,
            $notificationFactory,
            $logger,
            $serializer
        );
    }

    /**
     * Get Transaction ID from Response params.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getTransactionId(array $params): mixed
    {
        return $this->notificationUtilities->getTransactionId($params);
    }

    /**
     * Get action type from response params.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getActionType(array $params): mixed
    {
        return $this->notificationUtilities->getActionType($params);
    }

    /**
     * Method to find if notification should be processed from controller.
     *
     * @return bool
     */
    public function shouldProcessNotificationRealtime(): bool
    {
        return false;
    }

    /**
     * Get increment id from response params.
     *
     * @param array<mixed> $params
     * @return string|null
     */
    public function getIncrementId(array $params): ?string
    {
        return $this->notificationUtilities->getIncrementId($params);
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
}
