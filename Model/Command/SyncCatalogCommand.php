<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Command;

class SyncCatalogCommand
{
    public function syncCatalog(array $externalIds, bool $skipImages): array
    {
        $endPoint = '';
        $clientId = '';
        $clientSecret = '';
        $accessToken = '';
        $client = new \Attlaz\Client($endPoint);
        $client->setCredentials($clientId, $clientSecret, $accessToken);
        $client->setBranch('espa');
        $result = $client->scheduleTask('syncCatalog', [
            'externalIds' => $externalIds,
            'skipImages'  => $skipImages,
        ]);

        return $result;
    }
}