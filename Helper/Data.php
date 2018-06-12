<?php

namespace Attlaz\Base\Helper;

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

    public static function getExternalId(DataObject $dataObject): string
    {

        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId !== null && $externalId !== false) {
            return self::parseExternalId($externalId);
        }

        return '';
    }

    public static function hasExternalId(DataObject $dataObject): bool
    {
        $hasExternalId = $dataObject->hasData(Data::EXTERNAL_ID_FIELD);
        if (!$hasExternalId) {
            return false;
        }
        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId === null || $externalId === false || trim($externalId) === '') {
            return false;
        }

        return true;
    }

}
