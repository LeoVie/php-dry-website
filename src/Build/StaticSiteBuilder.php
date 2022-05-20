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
        $originalEnv = \Safe\file_get_contents(__DIR__ . '/../../.env');
        \Safe\copy(__DIR__ . '/../../.env.prod', __DIR__ . '/../../.env');

        $buildName = time();
        $buildDir = self::BUILDS_DIR . $buildName . '/';
        \Safe\mkdir($buildDir);

        $client = new Client(['base_uri' => 'http://web']);

        foreach (self::ROUTES as $route => $staticFilename) {
            $response = $client->request('GET', $route);

            $content = $this->copyAssets($buildDir, $response->getBody()->getContents());

            \Safe\file_put_contents($buildDir . $staticFilename, $content);
        }

        \Safe\file_put_contents(__DIR__ . '/../../.env', $originalEnv);
    }

    private function copyAssets(string $buildDir, string $content): string
    {
        $client = new Client(['base_uri' => 'http://web']);

        $hrefPattern = '@href="(.+?)"@';

        preg_match_all($hrefPattern, $content, $matches);

        foreach ($matches[1] as $url) {
            if (str_starts_with($url, '/')) {
                try {
                    $response = $client->request('GET', $url);

                    if ($response->getStatusCode() === 200) {
                        $pathParts = explode('/', ltrim($url, '/'));

                        array_pop($pathParts);

                        $this->createPathIfMissing($buildDir, join('/', $pathParts));

                        \Safe\file_put_contents(
                            $buildDir . ltrim($url, '/'),
                            $response->getBody()->getContents()
                        );
                    }
                } catch (GuzzleException $e) {
                }
            }
        }

        return $content;
    }

    private function createPathIfMissing(string $parentPath, string $path): void
    {
        $directories = explode('/', $path);

        if ($directories === [] || $directories[0] === '') {
            return;
        }

        $firstDirectory = array_shift($directories);

        if (!is_dir($parentPath . '/' . $firstDirectory)) {
            \Safe\mkdir($parentPath . '/' . $firstDirectory);
        }

        $this->createPathIfMissing(
            $parentPath . '/' . $firstDirectory,
            join('/', $directories)
        );
    }
}