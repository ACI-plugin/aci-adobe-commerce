<?php

namespace Aci\Payment\Gateway\Response;

use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Subscription\Model\ManageSubscriptionFrequency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TryzensIgnite\Common\Api\QuoteManagerInterface;
use TryzensIgnite\Subscription\Api\RecurringOrderRepositoryInterface;
use TryzensIgnite\Subscription\Api\Data\RecurringOrderHistoryInterfaceFactory;
use TryzensIgnite\Subscription\Api\RecurringOrderHistoryRepositoryInterface;
use TryzensIgnite\Subscription\Model\RecurringOrder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Class CreateSubscriptionResponseHandler - Class to handle subscription api response
 * CreateSubscription Response Handler
 */
class CreateSubscriptionResponseHandler implements HandlerInterface
{

    /**
     * @var QuoteManagerInterface
     */
    protected QuoteManagerInterface $quoteManager;

    /**
     * @var ManageSubscriptionFrequency
     */
    private ManageSubscriptionFrequency $frequencySession;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var RecurringOrderHistoryInterfaceFactory
     */
    protected RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory;

    /**
     * @var RecurringOrderHistoryRepositoryInterface
     */
    protected RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository;

    /**
     * @var AciGenericPaymentConfig
     */
    protected AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @param QuoteManagerInterface $quoteManager
     * @param ManageSubscriptionFrequency $frequencySession
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param Utilities $utilities
     * @param CheckoutSession $checkoutSession
     * @param RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory
     * @param RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     */
    public function __construct(
        QuoteManagerInterface             $quoteManager,
        ManageSubscriptionFrequency       $frequencySession,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Utilities                         $utilities,
        CheckoutSession                     $checkoutSession,
        RecurringOrderHistoryInterfaceFactory $recurringOrderHistoryInterfaceFactory,
        RecurringOrderHistoryRepositoryInterface $recurringOrderHistoryRepository,
        AciGenericPaymentConfig $aciGenericPaymentConfig
    ) {
        $this->quoteManager = $quoteManager;
        $this->frequencySession = $frequencySession;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->utilities = $utilities;
        $this->checkoutSession = $checkoutSession;
        $this->recurringOrderHistoryInterfaceFactory = $recurringOrderHistoryInterfaceFactory;
        $this->recurringOrderHistoryRepository = $recurringOrderHistoryRepository;
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
    }

    /**
     * Handles CreateSubscription transaction response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed>|null $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array|null $response): void
    {
        $responseStatus = null;
        if (isset($response['result']['code'])) {
            $resultCode = $response['result']['code'] ;
            $responseStatus = $this->utilities->validateResponse($resultCode);
        }
        if (isset($response['registrationId'])) {
            $this->updateSubscriptionsTable($response['registrationId'], $response['id']);
        }
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setSubscriptionStatus($responseStatus);
        $this->frequencySession->clearSubscriptionDataFromSession();
    }

    /**
     * Update/Insert data in subscription and subscription order tables
     *
     * @param string $registrationId
     * @param string $subscriptionId
     * @return void
     * @throws LocalizedException
     */
    private function updateSubscriptionsTable(string $registrationId, string $subscriptionId): void
    {
        $recurringOrder = $this->recurringOrderRepository->getByRegistrationId($registrationId);
        $recurringOrder->setSubscriptionId($subscriptionId);
        $recurringOrder->setStatus(RecurringOrder::SUB_STATUS_ACTIVE);
        $recurringOrder->setTestMode((string)$this->aciGenericPaymentConfig->getTestMode());
        $this->recurringOrderRepository->save($recurringOrder);
        $recurringOrderHistory = $this->recurringOrderHistoryInterfaceFactory->create();
        $recurringOrderHistory->setOrderId($recurringOrder->getLastOrderId());
        $recurringOrderHistory->setRegistrationId($registrationId);
        $this->recurringOrderHistoryRepository->save($recurringOrderHistory);
    }
}
