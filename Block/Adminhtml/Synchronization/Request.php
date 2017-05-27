<?php

namespace Attlaz\Base\Block\Adminhtml\Synchronization;

class Request extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_synchronization';
        $this->_blockGroup = 'Attlaz_Base';
        $this->_mode = 'request';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Send request'));
        $this->buttonList->remove('delete');
//        $this->buttonList->update('delete', 'label', __('Delete Condition'));
    }

    /**
     * Get Header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Catalog Synchronization');
//        if ($this->_coreRegistry->registry('checkout_agreement')
//                                ->getId()) {
//            return __('Edit Terms and Conditions');
//        } else {
//            return __('New Terms and Conditions');
//        }
    }
}
