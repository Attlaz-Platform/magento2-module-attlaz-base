<?php
declare(strict_types=1);

namespace Attlaz\Base\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Account extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $values = $element->getValues();
        $html = '<div id="attlaz_general_account_details">';
        $html .= '<ul class="checkboxes" id="attlaz_general_account_details_ul">';
        if ($values) {
            foreach ($values as $dat) {
                if ($dat['value'] !== '') {
                    $html .= "<li>{$dat['label']}: {$dat['value']}</li>";
                } else {
                    $html .= "<li>{$dat['label']}</li>";
                }
            }
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}
