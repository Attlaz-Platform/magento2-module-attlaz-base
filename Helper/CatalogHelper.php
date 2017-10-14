<?php

namespace Attlaz\Base\Helper;

class CatalogHelper extends Data
{

    public function shouldDisplayRealTimePrice(): bool
    {
        $value = intval($this->scopeConfig->getValue('attlaz/catalog/display_realtime_stock'));

        return ($value === 1);
    }

    public function shouldDisplayRealTimeStock(): bool
    {
        $value = intval($this->scopeConfig->getValue('attlaz/catalog/display_realtime_stock'));

        return ($value === 1);
    }
}
