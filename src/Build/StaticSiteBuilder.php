<?php

declare(strict_types=1);

namespace App\Build;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use LeoVie\PhpFilesystem\Service\FilesystemService;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\PcreException;

class StaticSiteBuilder
{
    private const BUILDS_DIR = __DIR__ . '/../../builds/';

    /**
     * @param array<string, string> $staticRoutesMapping
     * @param array<string> $dynamicRoutes
     */
    public function __construct(
        private FilesystemService $filesystemService,
        private ClientInterface   $webClient,
        /** @var array<string, string> */
        private array             $staticRoutesMapping,
        /** @var array<string> */
        private array             $dynamicRoutes,
    )
    {
    }

    /**
     * @throws FilesystemException
     * @throws GuzzleException
     */
    public function build(string $buildName): string
    {
        $buildDir = self::BUILDS_DIR . $buildName . '/';

        if (is_dir($buildDir)) {
            shell_exec('rm -rf ' . $buildDir);
        }

        \Safe\mkdir($buildDir);

        \Safe\file_put_contents($buildDir . 'BUILD_NAME', $buildName);

        $routesToStaticFilesMapping = array_merge(
            $this->staticRoutesMapping,
            $this->buildMappingFromDynamicRoutes()
        );

        foreach ($routesToStaticFilesMapping as $route => $staticFilename) {
            $response = $this->webClient->request('GET', $route);

            $content = $this->walkThroughLinks($buildDir, $response->getBody()->getContents(), $routesToStaticFilesMapping);

            $pathParts = explode('/', ltrim($staticFilename, '/'));
            array_pop($pathParts);

            $this->filesystemService->makeDirRecursive($buildDir, join('/', $pathParts));

            \Safe\file_put_contents($buildDir . $staticFilename, $content);
        }

        return $buildName;
    }

    /**
     * @param array<string, string> $routesToStaticFilesMapping
     *
     * @throws PcreException
     */
    private function walkThroughLinks(string $buildDir, string $content, array $routesToStaticFilesMapping): string
    {
        $linkPattern = '@(?>href|src)="(.+?)"@';

        preg_match_all($linkPattern, $content, $matches);

        /** @var string[] $fullMatches */
        $fullMatches = $matches[0];

        /** @var string[] $links */
        $links = $matches[1];

        foreach ($links as $i => $link) {
            $isInMapping = array_key_exists($link, $routesToStaticFilesMapping);
            if ($isInMapping) {
                $replacedLink = str_replace($link, $routesToStaticFilesMapping[$link], $fullMatches[$i]);
                $content = str_replace($fullMatches[$i], $replacedLink, $content);
                continue;
            }

            $isExternal = str_starts_with($link, '/') === false;
            if ($isExternal) {
                continue;
            }

            $isFileAsset = \Safe\preg_match('@.+\..{2,4}$@', $link) === 1;
            if ($isFileAsset) {
                $this->downloadAsset($link, $buildDir);
            }
        }

        return $content;
    }

    private function downloadAsset(string $url, string $buildDir): void
    {
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

    /**
     * @return array<string, string>
     * @throws JsonException
     */
    private function buildMappingFromDynamicRoutes(): array
    {
        $mapping = [];
        foreach ($this->dynamicRoutes as $dynamicRoute) {
            try {
                $response = $this->webClient->request('GET', $dynamicRoute);

                if ($response->getStatusCode() === 200) {
                    /** @var array{array{url: string}} $responseData */
                    $responseData = \Safe\json_decode($response->getBody()->getContents(), true);
                    foreach ($responseData as $entry) {
                        $url = $entry['url'];
                        $staticFilename = $url . '.html';

                        $mapping[$url] = $staticFilename;
                    }
                }
            } catch (GuzzleException $e) {
            }
        }

        return $mapping;
    }
}