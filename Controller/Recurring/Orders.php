<?php
namespace Aci\Payment\Controller\Recurring;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for recurring orders dashboard
 */
class Orders implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Customer Recurring Orders
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Recurring Orders'));
        return $resultPage;
    }
}
