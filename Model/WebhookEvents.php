<?php

namespace Aci\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;

/**
 * Manage webhook events for recurring orders
 */
class WebhookEvents
{
    /**
     * @var RecurringOrderManagement
     */
    protected RecurringOrderManagement $recurringOrderManagement;

    /**
     * @var CommandPoolInterface
     */
    protected CommandPoolInterface $commandPool;

    /**
     * @param RecurringOrderManagement $recurringOrderManagement
     * @param CommandPoolInterface $commandPool
     */
    public function __construct(
        RecurringOrderManagement $recurringOrderManagement,
        CommandPoolInterface $commandPool
    ) {
        $this->recurringOrderManagement = $recurringOrderManagement;
        $this->commandPool = $commandPool;
    }

    /**
     * Process events based on the response
     *
     * @param array<mixed> $params
     * @return string|null
     * @throws LocalizedException
     */
    public function processEvents(array $params): string|null
    {
        $transactionDetails = (array)$this->processResponse($params);
        return $this->createOrder($params, $transactionDetails);
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
        return $this->recurringOrderManagement->createRecurringOrder($params, $transactionDetails);
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
