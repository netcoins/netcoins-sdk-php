<?php
namespace Netcoins\Auth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Netcoins\Contracts\AuthInterface;

/**
 * Base auth class, containing essential auth features across all types.
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
abstract class Auth implements AuthInterface
{
    /**
     * @var Guzzle
     */
    protected $http;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $expiresAt;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Setup Auth with config.
     *
     * @param array  $config
     * @param string $prefix
     * @param Guzzle $http
     */
    public function __construct(array $config, string $prefix, Guzzle $http)
    {
        $this->prefix = $prefix;
        $this->http = $http;
        $this->setConfig($config);
    }

    /**
     * Resets & revokes authorization.
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function revoke(): void
    {
        $this->http->request('post', $this->prefix.'auth/revoke', [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Authorization' => "Bearer $this->token",
            ],
        ]);

        $this->token = null;
        $this->expiresAt = null;
        $this->response = null;
    }

    /**
     * Sets http request config.
     *
     * @param array $config
     *
     * @return void
     */
    private function setConfig(array $config): void
    {
        $defaults = [];
        $this->config = array_merge($defaults, $config);
    }

    /**
     * Gets auth config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Has auth expired?
     *
     * @return bool
     */
    public function isAuthExpired(): bool
    {
        $now = time();

        return !$this->token || ($this->token && $this->expiresAt <= $now);
    }

    /**
     * Gets auth token.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Sets API auth token.
     *
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Gets token expiry time.
     *
     * @return int|null
     */
    public function getTokenExpiry(): ?int
    {
        return $this->expiresAt;
    }
}
