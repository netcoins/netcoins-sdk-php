<?php
namespace Netcoins\Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Netcoins\Contracts\AuthInterface;
use Netcoins\Connector as NetcoinsConnector;

final class ConnectorTest extends \PHPUnit\Framework\TestCase
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

    /**
     *
     */
    public function testQueryReturnsArray()
    {
        $netcoins = $this->getNetcoins([
            new Response(200, [], json_encode([
                'BTC:CAD' => [
                    'buy' => '13731.31',
                    'sell' => '13571.80',
                ]
            ])),
        ]);

        // result is unimportant, looking at auth only here.
        $response = $netcoins->get('/prices', true);

        $this->assertIsArray($response);
        $this->assertIsArray($response['BTC:CAD']);
    }

    /**
     *
     */
    public function testConstructorSetsGuzzleIfNotSet()
    {
        $netcoins = new NetcoinsConnector([]);

        $this->assertInstanceOf(Guzzle::class, $netcoins->getHttpClient());
    }

    /**
     *
     */
    public function testConstructorSetsAuthIfNotSet()
    {
        $netcoins = new NetcoinsConnector([]);

        $this->assertInstanceOf(AuthInterface::class, $netcoins->getAuthHandler());
    }
}
