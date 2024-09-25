<?php

namespace Aci\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use TryzensIgnite\Subscription\Model\OrderManagement;
use TryzensIgnite\Subscription\Model\WebhookEvents as IgniteWebhookEvents;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface;
use TryzensIgnite\Common\Helper\Constants;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Model\OrderManagement as AciOrderManagement;

/**
 * Manage webhook events for recurring orders
 */
class WebhookEvents extends IgniteWebhookEvents
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var AciOrderManagement
     */
    protected AciOrderManagement $aciOrderManagement;

    /**
     * @param Utilities $utilities
     * @param AciOrderManagement $aciOrderManagement
     * @param OrderManagement $orderManagement
     * @param CommandPoolInterface $commandPool
     */
    public function __construct(
        Utilities $utilities,
        AciOrderManagement $aciOrderManagement,
        OrderManagement $orderManagement,
        CommandPoolInterface $commandPool
    ) {
        $this->utilities = $utilities;
        $this->aciOrderManagement = $aciOrderManagement;
        parent::__construct($orderManagement, $commandPool);
    }

    /**
     * Get transaction details from schedule response
     *
     * @param array<mixed> $params
     * @return ResultInterface|null|bool|array<mixed>
     * @throws NotFoundException|CommandException
     */
    public function processResponse(array $params): array|ResultInterface|bool|null
    {
        $paymentCommandParams = [
            Constants::TRANSACTION_ID => $this->getTransactionId($params)
        ];
        $response = $this->commandPool->get('getStatusViaWebhook')->execute($paymentCommandParams);
        if (!empty($response)) {
            return $this->parseResponse((array)$response);
        }
        return null;
    }

    /**
     * Get Transaction ID from webhook response
     *
     * @param array<mixed> $params
     * @return string
     */
    public function getTransactionId(array $params): string
    {
        return $params['payload']['id'] ?? '';
    }

    /**
     * Create the order from the previous recurring order
     *
     * @param array<mixed> $params
     * @param array<mixed> $transactionDetails
     * @return mixed
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function createOrder(array $params, array $transactionDetails): mixed
    {
        return $this->aciOrderManagement->createRecurringOrder($params, $transactionDetails);
    }

    /**
     * Parse transaction response from the full result
     *
     * @param array<mixed> $response
     * @return array<mixed>
     */
    private function parseResponse(array $response): array
    {
        if (!empty($response['records'])) {
            return $response['records'][0] ?? [];
        }

        return [];
    }
}
