<?php
namespace Netcoins\Tests\Auth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Netcoins\Auth\AuthClientCredentials as NetcoinsClientCredentials;

final class AuthClientCredentialsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Mock Guzzle
     *
     * @return Guzzle
     */
    private function getMockGuzzle(array $responses = [])
    {
        $mock = new MockHandler(array_merge([], $responses));

        $stack = HandlerStack::create($mock);
        return new Guzzle(['handler' => $stack]);
    }

    /**
     *
     */
    public function testAuthorizes()
    {
        $responses = [
            new Response(200, [], json_encode([
                'access_token' => 'Q3YUxsq4QHWrpxZ0Gequqdu15xCljrah',
                'expires_in' => time()+300,
            ]))
        ];

        $http = $this->getMockGuzzle($responses);
        $mock = $this->getMockForAbstractClass(NetcoinsClientCredentials::class, [[], 2, $http]);

        $mock->authorize();

        $this->assertEquals('Q3YUxsq4QHWrpxZ0Gequqdu15xCljrah', $mock->getToken());
        $this->assertFalse($mock->isAuthExpired());
    }
}
