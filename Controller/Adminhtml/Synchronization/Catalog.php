<?php

namespace Attlaz\Base\Controller\Adminhtml\Synchronization;

class Catalog extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
//        $id = $this->getRequest()
//                   ->getParam('id');
//        $agreementModel = $this->_objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
//
//        if ($id) {
//            $agreementModel->load($id);
//            if (!$agreementModel->getId()) {
//                $this->messageManager->addError(__('This condition no longer exists.'));
//                $this->_redirect('checkout/*/');
//
//                return;
//            }
//        }
//
//        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)
//                                     ->getAgreementData(true);
//        if (!empty($data)) {
//            $agreementModel->setData($data);
//        }
//
//        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()//->_addBreadcrumb('Catalog synchronisation')
             ->_addContent($this->_view->getLayout()
                                       ->createBlock(\Attlaz\Base\Block\Adminhtml\Synchronization\Request::class)
                                       ->setData('action', $this->getUrl('*/*/save')));
        $this->_view->getPage()
                    ->getConfig()
                    ->getTitle()
                    ->prepend(__('Catalog synchronisation'));
//        $this->_view->getPage()
//                    ->getConfig()
//                    ->getTitle()
//                    ->prepend($agreementModel->getId() ? $agreementModel->getName() : __('New Condition'));
        $this->_view->renderLayout();
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
