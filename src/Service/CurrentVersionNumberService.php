<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\ClientInterface;

class CurrentVersionNumberService
{
    const PACKAGIST_API_ENDPOINT = 'leovie/php-dry.json';

    public function __construct(
        private ClientInterface $packagistClient
    )
    {
    }

    public function getFromPackagist(): string
    {
        if ($_ENV['APP_ENV'] !== 'prod') {
            return '0.0.0';
        }

        $response = $this->packagistClient->request('GET', self::PACKAGIST_API_ENDPOINT);

        /** @var array{'packages': array{'leovie/php-dry': array{0: array{'version': string}}}} $responseData */
        $responseData = \Safe\json_decode($response->getBody()->getContents(), true);

        $version = $responseData['packages']['leovie/php-dry'][0]['version'];

        return substr($version, 1);
    }
}