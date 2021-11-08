<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Tests\MockFacades;

use GuzzleHttp\Client as GuzzleClient;
use Urvin\Gokaru\Client;

class HttpMockableClient extends Client
{
    public function getHttpClient(): GuzzleClient
    {
        return parent::getHttpClient();
    }

    public function setHttpClient(GuzzleClient $client): void
    {
        $this->httpClient = $client;
    }
}