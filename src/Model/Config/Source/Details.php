<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Details implements OptionSourceInterface
{
    private array|null $_options = null;
    /**
     * @var \Attlaz\Base\Helper\Data
     */
    private $_helper = null;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_message;
    private $storeManager;
    private string $_error = '';

    /**
     * Details constructor.
     * @param \Ebizmarts\MailChimp\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $message
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Attlaz\Base\Helper\Data                    $helper,
        \Magento\Framework\Message\ManagerInterface $message,
        \Magento\Store\Model\StoreManager           $storeManager,
        \Magento\Framework\App\RequestInterface     $request
    )
    {
        $this->_message = $message;
        $this->_helper = $helper;
        $this->storeManager = $storeManager;
        $storeId = (int)$request->getParam("store", 0);
        if ($request->getParam('website', 0)) {
            $scope = 'website';
            $storeId = $request->getParam('website', 0);
        } elseif ($request->getParam('store', 0)) {
            $scope = 'stores';
            $storeId = $request->getParam('store', 0);
        } else {
            $scope = 'default';
        }
        if ($this->_helper->hasClientConfiguration()) {
            $this->_options['module_version'] = $this->_helper->getModuleVersion();
            try {
                $this->_options['api_endpoint'] = $this->_helper->getApiEndpoint();
                $this->_options['api_version'] = $this->_helper->getClient()->getApiVersion();
                if ($this->_helper->hasProjectEnvironmentIdentifier()) {
                    $this->_options['environment'] = $this->_helper->getProjectIdentifier();
                }
                $this->_options['connected'] = true;
            } catch (\Exception $e) {
                //$this->_helper->log($e->getFriendlyMessage());
                $this->_error = $e->getMessage();
                $this->_options['connected'] = false;
            }
        } else {
            $this->_options = ['--- Enter your API Key ---'];
        }
    }

    public function toOptionArray()
    {

        $res = [];
        if (is_array($this->_options)) {
            foreach ($this->_options as $option => $value) {
                $formattedLabel = ucwords(str_replace(['-', '_'], [' ', ' '], (string)$option));
                $res[] = ['label' => $formattedLabel, 'value' => $value];
            }
        } elseif (!$this->_options) {
            $res = [['label' => 'Error', 'value' => __('--- Invalid API Key ---')]];
        } else {
            $res = [['label' => 'Important', 'value' => __($this->_options)]];
        }
        return $res;
    }
}
