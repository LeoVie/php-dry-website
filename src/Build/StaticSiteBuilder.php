<?php

declare(strict_types=1);

namespace App\Build;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Safe\Exceptions\FilesystemException;

class StaticSiteBuilder
{
    private const BUILDS_DIR = __DIR__ . '/../../builds/';

    private const ROUTES = [
        '/' => 'index.html'
    ];

    /**
     * @throws FilesystemException
     * @throws GuzzleException
     */
    public function build(): void
    {
        $buildName = time();
        $buildDir = self::BUILDS_DIR . $buildName . '/';
        mkdir($buildDir);

        $client = new Client(['base_uri' => 'http://web']);

        foreach (self::ROUTES as $route => $staticFilename) {
            $response = $client->request('GET', $route);

            \Safe\file_put_contents($buildDir . $staticFilename, $response->getBody()->getContents());
        }
    }
}