<?php

declare(strict_types=1);

namespace App\Build;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use LeoVie\PhpFilesystem\Service\FilesystemService;
use Safe\Exceptions\FilesystemException;

class StaticSiteBuilder
{
    private const BUILDS_DIR = __DIR__ . '/../../builds/';

    private const ROUTES = [
        '/' => 'index.html'
    ];

    public function __construct(
        private FilesystemService $filesystemService,
        private ClientInterface $webClient
    )
    {
    }

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

        \Safe\file_put_contents($buildDir . 'BUILD_NAME', $buildName);

        foreach (self::ROUTES as $route => $staticFilename) {
            $response = $this->webClient->request('GET', $route);

            $content = $this->copyAssets($buildDir, $response->getBody()->getContents());

            \Safe\file_put_contents($buildDir . $staticFilename, $content);
        }

        \Safe\file_put_contents(__DIR__ . '/../../.env', $originalEnv);
    }

    private function copyAssets(string $buildDir, string $content): string
    {
        $hrefPattern = '@(?>href|src)="(.+?)"@';

        preg_match_all($hrefPattern, $content, $matches);

        foreach ($matches[1] as $url) {
            if (str_starts_with($url, '/')) {
                try {
                    $response = $this->webClient->request('GET', $url);

                    if ($response->getStatusCode() === 200) {
                        $pathParts = explode('/', ltrim($url, '/'));

                        array_pop($pathParts);

                        $this->filesystemService->makeDirRecursive($buildDir, join('/', $pathParts));

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
}