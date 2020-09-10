<?php
namespace Netcoins\Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

use Netcoins\Connector as NetcoinsConnector;

final class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Mock Guzzle and retrieve a netcoins instance
     *
     * @return NetcoinsConnector
     */
    private function getNetcoins(array $responses = [])
    {
        $mock = new MockHandler(array_merge([
            new Response(200, [], json_encode([
                'access_token' => 'Q3YUxsq4QHWrpxZ0Gequqdu15xCljrah',
                'expires_in' => time()+300,
            ]))
        ], $responses));

        $stack = HandlerStack::create($mock);
        $http = new Guzzle(['handler' => $stack]);

        return new NetcoinsConnector([], 2, $http);
    }
}
