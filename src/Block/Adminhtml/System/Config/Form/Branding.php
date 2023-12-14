<?php
declare(strict_types=1);

namespace Attlaz\Base\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Branding extends Field
{
    /** @var string */
    protected $_template = 'Attlaz_Base::system/config/branding.phtml';

    /**
     * Render field
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo(): string
    {
        return $this->getViewFileUrl('Attlaz_Base::images/logo.svg');
    }

    /**
     * Get website url
     *
     * @return string
     */
    public function getWebsiteUrl(): string
    {
        return 'https://attlaz.com/';
    }

    /**
     * Get application url
     *
     * @return string
     */
    public function getAppUrl(): string
    {
        return 'https://app.attlaz.com/';
    }
}
