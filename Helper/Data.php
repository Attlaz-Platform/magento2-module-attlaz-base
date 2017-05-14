<?php

namespace Attlaz\Base\Helper;

use Attlaz\Base\Model\Config\Source\CustomerType;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObject;

class Data extends AbstractHelper
{

    const EXTERNAL_ID_FIELD = 'attlaz_external_id';
    const SYNC_TIME_FIELD = 'attlaz_sync_time';
    const BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA = '_realtime';

    private static function parseExternalId(string $externalId, string $key = 'dbfact_id'): string
    {
        $externalId = trim($externalId);
        if (substr($externalId, 0, 1) === '{') {

            $externalIdObject = json_decode($externalId, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($externalIdObject[$key])) {
                    return $externalIdObject[$key];
                }
            } else {

                throw new \Exception('Unable to parse external id (' . $externalId . ', ' . json_last_error() . ')');
            }
        }
        if (strpos($externalId, ':') !== false) {
            throw new \Exception('Unable to parse external id (' . $externalId . ')');
        }

        return $externalId;
    }

    protected function getConfigShowPricesForCustomer(): int
    {
        //TODO: remove debug
        return CustomerType::TYPE_ALL;

        return intval($this->scopeConfig->getValue('attlaz/catalog/show_prices_for_customer'));
    }

    protected function getConfigShowStockForCustomer(): int
    {
        //TODO: remove debug
        return CustomerType::TYPE_ALL;

        return intval($this->scopeConfig->getValue('attlaz/catalog/show_stock_for_customer'));
    }

    public static function getExternalId(DataObject $dataObject): string
    {
        //Todo: remove debug
        // return $category->getId();

        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId !== null && $externalId !== false) {
            return self::parseExternalId($externalId);
        }

        return '';
    }

    public static function hasExternalId(DataObject $customer): bool
    {
        $hasExternalId = $customer->hasData(Data::EXTERNAL_ID_FIELD);
        if (!$hasExternalId) {
            return false;
        }
        $externalId = $customer->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId === null || $externalId === false || trim($externalId) === '') {
            return false;
        }

        return true;
    }

}
