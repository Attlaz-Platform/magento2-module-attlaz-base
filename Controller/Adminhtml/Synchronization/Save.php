<?php

namespace Attlaz\Base\Controller\Adminhtml\Synchronization;

use Attlaz\Base\Model\Command\SyncCatalogCommand;
use Magento\Framework\Controller\Result\RedirectFactory;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $redirectFactory;
    /** @var SyncCatalogCommand */
    protected $syncCatalogCommand;

    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context, RedirectFactory $redirectFactory, SyncCatalogCommand $syncCatalogCommand, LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->redirectFactory = $redirectFactory;
        $this->syncCatalogCommand = $syncCatalogCommand;
        $this->logger = $logger;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $externalIds = $this->getParamExternalIds();
            $skipImages = $this->getParamSkipImages();
            $result = $this->syncCatalogCommand->syncCatalog($externalIds, $skipImages);

            if ($result['success'] === true) {
                $id = $result['id'];
                $this->messageManager->addSuccessMessage('Synchronization is pending (Task: ' . $id . ')');
            } else {
                $this->messageManager->addErrorMessage('Unable to sync catalog');
                $this->logger->error('Unable to sync catalog: unknown issue');
            }
        } catch (\Throwable $ex) {
            $this->messageManager->addErrorMessage('Unable to sync catalog: ' . $ex->getMessage());
            //$this->logger->error('Unable to sync catalog: ' . $ex->getMessage());
        }

        return $this->redirectFactory->create()
                                     ->setUrl($this->getUrl('*/*/catalog'));
    }

    private function getParamExternalIds(): array
    {
        $externalIds = $this->getRequest()
                            ->getParam('external_ids');

        $arrExternalIds = \explode(\PHP_EOL, $externalIds);
        $arrExternalIds = array_map('trim', $arrExternalIds);

        return $arrExternalIds;
    }

    private function getParamSkipImages(): bool
    {
        $skipImages = $this->getRequest()
                           ->getParam('skip_images');

        return (string)$skipImages === '1';
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Attlaz_Base::catalog_synchronization')
             ->_addBreadcrumb(__('Sales'), __('Sales'))
             ->_addBreadcrumb(__('Checkout Conditions'), __('Checkout Terms and Conditions'));

        return $this;
    }
}
