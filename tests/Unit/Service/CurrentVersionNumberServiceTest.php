<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\CurrentVersionNumberService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class CurrentVersionNumberServiceTest extends TestCase
{
    public function testGetFromPackagist(): void
    {
        $body = \Safe\json_encode([
            'packages' => [
                'leovie/php-dry' => [
                    [
                        'version' => 'v1.2.3',
                    ],
                ],
            ],
        ]);

        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $currentVersionNumberService = new CurrentVersionNumberService($client);

        self::assertSame('1.2.3', $currentVersionNumberService->getFromPackagist());
    }
}