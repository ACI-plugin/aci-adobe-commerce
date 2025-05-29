<?php

namespace Aci\Payment\Controller\Adminhtml\DownloadLogs;

use Aci\Payment\Logger\AciHandler;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Aci\Payment\Model\Adminhtml\DownloadLogs\DownloadLogsBase;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem\Io\File;

/**
 *  Controller for downloading payment logs
 */
class DownloadLogs extends Action
{
    /**
     * @var DownloadLogsBase
     */
    private DownloadLogsBase $downloadLogBase;

    /**
     * @var AciHandler
     */
    private AciHandler $handler;

    /**
     * @var DirectoryList
     */
    private DirectoryList $dirList;

    /**
     * @var RawFactory
     */
    private RawFactory $resultRawFactory;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param AciHandler $handler
     * @param DownloadLogsBase $downloadLogBase
     * @param Context $context
     * @param DirectoryList $dirList
     * @param RawFactory $resultRawFactory
     * @param File $file
     */
    public function __construct(
        AciHandler     $handler,
        DownloadLogsBase $downloadLogBase,
        Context          $context,
        DirectoryList    $dirList,
        RawFactory $resultRawFactory,
        File $file
    ) {
        parent::__construct($context);
        $this->downloadLogBase = $downloadLogBase;
        $this->handler = $handler;
        $this->dirList = $dirList;
        $this->resultRawFactory = $resultRawFactory;
        $this->file = $file;
    }

    /**
     * Downloads the zipped log file
     *
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        try {
            $location = $this->handler->getLogLocation();
            $destination = $this->dirList->getPath('log').'/aci_payment_log.zip';
            $zipFileLocation = $this->downloadLogBase->getZip($location, $destination);
            if ($zipFileLocation) {
                $resultRaw = $this->resultRawFactory->create();
                $fileName = 'aci_payment_log.zip';
                // phpcs:ignore
                $fileSize = filesize($zipFileLocation);
                $fileContents = $this->file->read($zipFileLocation);
                $resultRaw->setHeader('Content-Type', 'application/zip');
                $resultRaw->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                $resultRaw->setHeader('Content-Length', (string)$fileSize);
                $resultRaw->setContents((string)$fileContents);
                $this->removeLogFile($zipFileLocation);
                return $resultRaw;
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while downloading the log .
                Log files might not be generated yet. ') .
                ' ' .
                $e->getMessage()
            );

        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();
        return $resultRedirect;
    }

    /**
     * Remove the generated logfile
     *
     * @param string $zipFileLocation
     * @return bool
     */
    public function removeLogFile(string $zipFileLocation): bool
    {
        if ($this->file->fileExists($zipFileLocation)) {
            $this->file->rm($zipFileLocation);
        }
        return true;
    }
}
