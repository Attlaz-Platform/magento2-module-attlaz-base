<?php
declare(strict_types=1);

namespace Attlaz\Base\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;


class Branding extends Field
{


    protected $_template = 'Attlaz_Base::system/config/branding.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    public function getLogo(): string
    {
        return $this->getViewFileUrl('Attlaz_Base::images/logo.svg');
    }

    public function getWebsiteUrl(): string
    {
        return 'https://attlaz.com/';
    }

    public function getAppUrl(): string
    {
        return 'https://app.attlaz.com/';
    }
}
