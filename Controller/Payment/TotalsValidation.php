<?php
namespace Aci\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Model\Quote;

/**
 * Class TotalsValidation - Returns current grandtotal of quote
 */
class TotalsValidation implements ActionInterface
{
    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @param Session $checkoutSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Session $checkoutSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Returns current grandtotal of quote
     *
     * @return Json
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): Json
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $totalAmount = $quote->getGrandTotal();
        $result = $this->resultJsonFactory->create();
        return $result->setData(['total' => $totalAmount]);
    }
}
