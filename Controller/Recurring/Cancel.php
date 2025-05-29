<?php
namespace Aci\Payment\Controller\Recurring;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Aci\Payment\Model\Api\CancelSubscriptionInterface;
use TryzensIgnite\Base\Model\Api\ResponseInterface as IgniteResponseInterface;
use Aci\Payment\Model\RecurringOrder;
use Aci\Payment\Model\Subscription\ManageSubscription;

/**
 * Controller for cancelling recurring order
 */
class Cancel implements ActionInterface
{
    public const SUBSCRIPTION_CANCEL_SUCCESS_MESSAGE = 'Subscription cancelled successfully.';
    public const SUBSCRIPTION_CANCEL_ERROR_MESSAGE = 'Something went wrong while canceling the subscription.';
    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var CancelSubscriptionInterface
     */
    private CancelSubscriptionInterface $cancelSubscription;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var IgniteResponseInterface
     */
    private IgniteResponseInterface $response;

    /**
     * @var ManageSubscription
     */
    private ManageSubscription $subscription;

    /**
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param CancelSubscriptionInterface $cancelSubscription
     * @param ManageSubscription $subscription
     * @param IgniteResponseInterface $response
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ResultRedirectFactory $resultRedirectFactory,
        Context               $context,
        CancelSubscriptionInterface $cancelSubscription,
        ManageSubscription $subscription,
        IgniteResponseInterface $response,
        ManagerInterface       $messageManager
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->cancelSubscription = $cancelSubscription;
        $this->subscription = $subscription;
        $this->response = $response;
        $this->messageManager = $messageManager;
    }

    /**
     * Calls CancelSubscription API
     *
     * @return ResultInterface|ResponseInterface|Redirect
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $params = $this->context->getRequest()->getParams();
        $subscriptionIdParamKey = $this->subscription->getSubscriptionIdKey();
        $subscriptionId = $params[$subscriptionIdParamKey];
        /** @phpstan-ignore-next-line */
        $result = $this->cancelSubscription->cancelSubscription($subscriptionId);
        $resultCode = $result['result']['code'];
        if ($resultCode && $this->response->isSuccessResponse($resultCode)) {
            $this->subscription->updateSubscriptionStatus($subscriptionId, RecurringOrder::SUB_STATUS_CANCELLED);
            $this->addSuccessMessage();
        } else {
            $this->addErrorMessage();
        }
        return $this->resultRedirectFactory->create()->setPath('acipayment/recurring/orders');
    }

    /**
     * Add subscription cancellation success message
     *
     * @return void
     */
    public function addSuccessMessage():void
    {
        $this->messageManager->addSuccessMessage(self::SUBSCRIPTION_CANCEL_SUCCESS_MESSAGE);
    }

    /**
     * Add subscription cancellation error message
     *
     * @return void
     */
    public function addErrorMessage():void
    {
        $this->messageManager->addErrorMessage(self::SUBSCRIPTION_CANCEL_ERROR_MESSAGE);
    }
}
