<?php
namespace Aci\Payment\Controller\Recurring;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Aci\Payment\Model\ManageSubscriptionFrequency;

/**
 * Class Recurring Order - Check the frequency and save it to session
 */
class RecurringOrder implements ActionInterface
{
    /**
     * @var ManageSubscriptionFrequency
     */
    protected ManageSubscriptionFrequency $frequencySession;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @param ManageSubscriptionFrequency $frequencySession
     * @param JsonFactory $resultJsonFactory
     * @param Request $request
     * @param Serializer $serializer
     */
    public function __construct(
        ManageSubscriptionFrequency $frequencySession,
        JsonFactory                 $resultJsonFactory,
        Request                     $request,
        Serializer                  $serializer,
    ) {
        $this->frequencySession = $frequencySession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->serializer = $serializer;
    }

    /**
     * Returns current grandtotal of quote
     *
     * @return Json
     */
    public function execute(): Json
    {
        $subscriptionUnit = null;
        $subscriptionFrequency = null;
        $subscriptionFrequencyData = $this->request->getContent();
        $parsedData = $this->serializer->unserialize($subscriptionFrequencyData);
        $this->frequencySession->clearSubscriptionDataFromSession();
        if (isset($parsedData['recurring_options']['unit']) && isset($parsedData['recurring_options']['valueOfUnit'])) {
            $subscriptionUnit = $parsedData['recurring_options']['unit'];
            $subscriptionFrequency = $parsedData['recurring_options']['valueOfUnit'];
        }
        $result = $this->resultJsonFactory->create();
        if ($subscriptionUnit) {
            $this->frequencySession->storeSubscriptionDataInSession($subscriptionFrequency, $subscriptionUnit);
            return $result->setData(['status' => 'success']);
        } else {
            return $result->setData(['status' => 'failed']);
        }
    }
}
