<?php
namespace Netcoins\Tests;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Netcoins\Auth\AuthClientCredentials;
use Netcoins\Contracts\ApiInterface;
use Netcoins\Client as NetcoinsClient;
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

        $auth = new AuthClientCredentials([], 'api/v2/', $http);

        return new NetcoinsConnector([], 2, $http, $auth);
    }

    /**
     *
     */
    public function testPricesReturnSingularIfAssetSpecified()
    {
        $expected = [
            'buy' => '13731.31',
            'sell' => '13571.80',
        ];

        $connector = $this->getNetcoins([
            new Response(200, [], json_encode([
                'BTC:CAD' => $expected,
                'LTC:CAD' => [
                    'buy' => '64.07',
                    'sell' => '64.21',
                ]
            ])),
        ]);

        $netcoins = new NetcoinsClient([], 2, $connector);

        $btccad = $netcoins->prices('BTC', 'CAD');

        $this->assertEquals($expected, $btccad);
    }

    /**
     *
     */
    public function testConstructorSetsConnectorIfNoneGiven()
    {
        $netcoins = new NetcoinsClient();

        $this->assertInstanceOf(ApiInterface::class, $netcoins->getAPIConnector());
    }
}
