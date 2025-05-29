<?php
namespace Aci\Payment\Model\Request;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use TryzensIgnite\Base\Api\QuoteManagerInterface;
use TryzensIgnite\Base\Model\Api\RequestManager;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Logger\AciLogger;

/**
 * Process CreateSubscription Request
 */
class CreateSubscriptionRequestManager extends RequestManager
{

    /**
     * CreateSubscriptionRequestManager constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManagerInterface $quoteManager
     * @param AciLogger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManagerInterface $quoteManager,
        AciLogger $logger,
        string $commandName = 'createSubscription'
    ) {
        parent::__construct(
            $commandPool,
            $paymentDataObjectFactory,
            $quoteManager,
            $logger,
            $commandName
        );
    }

    /**
     * Process Api request
     *
     * @param array<mixed> $requestParams
     * @return ResultInterface|null|bool|array<mixed>
     * @throws LocalizedException
     */
    public function process(array $requestParams): array|ResultInterface|bool|null
    {
        $result = [];
        try {
            if ($this->commandName) {
                $registrationId = $requestParams['registrationId'] ?? null;
                $checkoutId = $requestParams[Constants::KEY_CHECKOUT_ID] ?? null;
                $paymentCommandParams = [
                    Constants::KEY_CHECKOUT_ID => $checkoutId,
                    'registrationId' => $registrationId,
                    'requestParams' => $requestParams,
                    'SchedulerCall' => true
                ];
                /** @var ResultInterface $result */
                $result = $this->commandPool->get($this->commandName)->execute($paymentCommandParams);
            }
            return $result;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

}
