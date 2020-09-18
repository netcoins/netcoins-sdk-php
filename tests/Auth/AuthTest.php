<?php
namespace Netcoins\Tests\Auth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Netcoins\Auth\Auth as NetcoinsAuth;

final class AuthTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Mock Guzzle and return
     *
     * @return Guzzle
     */
    private function getMockGuzzle()
    {
        $mock = new MockHandler(array_merge([
            new Response(200, [], json_encode([]))
        ]));

        $stack = HandlerStack::create($mock);
        return new Guzzle(['handler' => $stack]);
    }

    /**
     *
     */
    public function testRevokeEmptysAuth()
    {
        $http = $this->getMockGuzzle();
        $auth = $this->getMockForAbstractClass(NetcoinsAuth::class, [[], 2, $http]);

        $auth->setToken('Q3YUxsq4QHWrpxZ0Gequqdu15xCljrah');
        $auth->revoke();

        $this->assertEquals(null, $auth->getToken());
        $this->assertEquals(null, $auth->getTokenExpiry());
        $this->assertTrue($auth->isAuthExpired());
    }
}
