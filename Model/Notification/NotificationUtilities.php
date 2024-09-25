<?php

namespace Aci\Payment\Model\Notification;

use Aci\Payment\Helper\Constants;
use Aci\Payment\Logger\AciLogger;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

class NotificationUtilities
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var AciLogger
     */
    private AciLogger $logger;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AciLogger $logger
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        OrderRepositoryInterface        $orderRepository,
        SearchCriteriaBuilder           $searchCriteriaBuilder,
        AciLogger                       $logger
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Get Transaction ID from Response params.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getTransactionId(array $params): mixed
    {
        if (isset($params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_REFERENCED_ID])) {
            return $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_REFERENCED_ID];
        } else {
            return $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_CHECKOUT_ID];
        }
    }

    /**
     * Get action type from response params.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getActionType(array $params): mixed
    {
        if (isset($params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE])) {
            return $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE];
        }
        return null;
    }

    /**
     * Get increment id from response params.
     *
     * @param array<mixed> $params
     * @return string|null
     */
    public function getIncrementId(array $params): ?string
    {
        if (isset($params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID])) {
            return $params [Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID];
        } else {
            $transactionId = $this->getTransactionId($params);
            $this->searchCriteriaBuilder->addFilter('txn_id', $transactionId);
            $this->searchCriteriaBuilder->setPageSize(1);
            $list = $this->transactionRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            $list = $list->getItems();
            $orderId = 0;
            foreach ($list as $transaction) {
                $orderId = $transaction->getOrderId();
                break;
            }
            if ($orderId) {
                $order = $this->orderRepository->get($orderId);
                return $order->getIncrementId();
            }
            $this->logger->error(__('Cannot resolve order increment id from transaction id - '.$transactionId));
            return null;
        }
    }

    /**
     * Get transaction amount from input array.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getTransactionAmount(array $params): mixed
    {
        return $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PAYMENT_AMOUNT] ??
            $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_ACI_PRESENTATION_AMOUNT] ?? null;
    }

    /**
     * Get payment brand  from input array.
     *
     * @param array<mixed> $params
     * @return mixed
     */
    public function getPaymentBrand(array $params): mixed
    {
        return $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_PAYMENT_BRAND] ??
            $params[Constants::KEY_NOTIFICATION_PAYLOAD][Constants::KEY_PAYMENT_BRAND] ?? null;
    }

    /**
     * Check if the api response is of scheduler API call
     *
     * @param array<mixed> $params
     * @return bool
     */
    public function isSchedulerResponse(array $params): bool
    {
        if (isset($params['type']) &&
            $params['type'] === Constants::RESPONSE_TYPE_SCHEDULE) {
            return true;
        }
        return false;
    }
}
