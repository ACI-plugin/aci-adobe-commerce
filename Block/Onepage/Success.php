<?php
namespace Aci\Payment\Block\Onepage;

use Aci\Payment\Model\Ui\AciApmConfigProvider;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Magento\Checkout\Block\Onepage\Success as CoreSuccess;
use TryzensIgnite\Common\Api\OrderManagerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Aci\Payment\Helper\Constants;

/**
 * Get Additional information for checkout success page - Pending payment
 */
class Success extends CoreSuccess
{
    public const PENDING_REVIEW_NOTE = 'We are reviewing  your payment. We will get back to you shortly';

    /**
     * @var array<mixed>
     */
    protected array $aciPaymentMethods = [
        AciCcConfigProvider::CODE,
        AciApmConfigProvider::CODE
    ];

    /**
     * Get Payment method from the last real order
     *
     * @return string
     */
    public function getPaymentMethod(): string
    {
        /** @var OrderPaymentInterface $paymentInstance */
        $paymentInstance = $this->_checkoutSession->getLastRealOrder()->getPayment();
        return $paymentInstance->getMethod();
    }

    /**
     * Get Order status from the last real order
     *
     * @return float|string|null
     */
    public function getOrderStatus(): float|string|null
    {
        return $this->_checkoutSession->getLastRealOrder()->getState();
    }

    /**
     * Get additional info for ACI payment with pending review status
     *
     * @return string
     */
    public function getAdditionalInfo(): string
    {
        $orderStatus = $this->getOrderStatus();
        $paymentMethod = $this->getPaymentMethod();

        $returnMessage = '';
        if (in_array($paymentMethod, $this->aciPaymentMethods)
            && $orderStatus === OrderManagerInterface::ORDER_STATUS_PAYMENT_REVIEW) {
            $returnMessage = self::PENDING_REVIEW_NOTE;
        } else {
            /** @phpstan-ignore-next-line */
            $subscriptionStatus = $this->_checkoutSession->getSubscriptionStatus();
            if ($subscriptionStatus == Constants::PENDING) {
                $returnMessage = self::PENDING_REVIEW_NOTE;
                /** @phpstan-ignore-next-line */
                $this->_checkoutSession->unsSubscriptionStatus();
            }
        }
        return $returnMessage;
    }
}
