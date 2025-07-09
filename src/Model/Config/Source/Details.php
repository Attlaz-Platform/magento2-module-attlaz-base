<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Details implements OptionSourceInterface
{
    private array|null $_options = null;

    /**
     * Details constructor.
     * @param Data $_helper
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly Data $_helper,
        RequestInterface      $request
    )
    {

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
        $this->_options['module_version'] = $this->_helper->getModuleVersion();
        if ($this->_helper->hasClientConfiguration()) {

            try {
                $this->_options['api_endpoint'] = $this->_helper->getApiEndpoint();

                $client = $this->_helper->getClient();
                if ($client !== null) {
                    $this->_options['api_version'] = $client->getApiVersion();
                }


                if ($this->_helper->hasProjectEnvironmentIdentifier()) {
                    $this->_options['environment'] = $this->_helper->getProjectIdentifier();
                }
                $this->_options['connected'] = 'Yes';
            } catch (\Exception $e) {
                //$this->_helper->log($e->getFriendlyMessage());
                // $this->_error = $e->getMessage();
                $this->_options['connected'] = 'No';
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
