<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;

class CurrentVersionNumberService
{
    public function getFromPackagist(): string
    {
        $client = new Client(['base_uri' => 'https://repo.packagist.org/p2/']);

        $response = $client->request('GET', 'leovie/php-dry.json');

        $responseData = \Safe\json_decode($response->getBody()->getContents(), true);

        $version = $responseData['packages']['leovie/php-dry'][0]['version'];

        return substr($version, 1);
    }
}