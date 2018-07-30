<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Command;

use Attlaz\Base\Model\Resource\BaseResource;
use Attlaz\Model\ScheduleTaskResult;

class SyncCatalogCommand
{
    private $resource;

    public function __construct(BaseResource $baseResource)
    {
        $this->resource = $baseResource;
    }

    public function syncCatalog(array $externalIds, bool $skipImages): ScheduleTaskResult
    {
        $result = $this->resource->executeTaskByCommand('syncCatalog', [
            'externalIds' => $externalIds,
            'skipImages'  => $skipImages,
        ]);

        return $result;
    }
}