<?php
declare(strict_types=1);

namespace Aci\Payment\Controller\Process;

use Exception;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Aci\Payment\Model\Transaction\AciTransactionManager;
use Aci\Payment\Helper\Constants as AciConstants;
use Aci\Payment\Logger\AciLogger;

/**
 * Process response after redirect
 */
class Response implements ActionInterface
{
    public const ERR_MSG_GENERIC =   'Something went wrong while processing the request.';

    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var AciTransactionManager
     */
    private AciTransactionManager $transactionManager;

    /**
     * @var AciLogger
     */
    private AciLogger $logger;

    /**
     * @var string
     */
    protected string $method = '';

    /**
     * Response constructor.
     *
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param AciTransactionManager $transactionManager
     * @param AciLogger $logger
     */
    public function __construct(
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        ManagerInterface $messageManager,
        AciTransactionManager $transactionManager,
        AciLogger $logger
    ) {
        $this->resultRedirectFactory    =   $resultRedirectFactory;
        $this->context                  =   $context;
        $this->messageManager           =   $messageManager;
        $this->transactionManager       =   $transactionManager;
        $this->logger                   =   $logger;
    }

    /**
     * Process redirect response
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        try {
            $params = $this->context->getRequest()->getParams();
            $this->logger->info('ShopperResultUrl params : ', $params);
            $checkoutId = $params[AciConstants::URL_PARAM_ID] ?? null;
            $resourcePath = $params[AciConstants::URL_PARAM_RESOURCE_PATH] ?? null;
            if ($checkoutId && $resourcePath) {
                $processStatus = $this->transactionManager->processSuccessResponse(
                    $params,
                    $this->method
                );
                //If checkout transaction is success, redirect to order success page
                if (!empty($processStatus['status'])) {
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
                }
            } else {
                $params[AciConstants::URL_PARAM_ID] = $checkoutId;
                $this->transactionManager->processFailedResponse($params);
            }
            return $this->redirectToCart(self::ERR_MSG_GENERIC);

        } catch (Exception $e) {
            $this->logger->error('Process Response Error: '. $e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Redirects to cart
     *
     * @param string $message
     * @return Redirect
     */
    public function redirectToCart(string $message):Redirect
    {
        $this->messageManager->addErrorMessage($message);
        return $this->resultRedirectFactory->create()->setPath('checkout/cart');
    }
}
