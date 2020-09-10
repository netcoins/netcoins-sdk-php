<?php
namespace Netcoins;

use GuzzleHttp\Client as Guzzle;
use Netcoins\Contracts\ApiInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * A class to handle connection to API via guzzle.
 * Handles auth & revoke methods, and querying the API
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
class Connector implements ApiInterface
{
    /**
     * @var Guzzle
     */
    private $http;

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var array
     */
    private $auth = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $expiresAt;

    /**
     * Setup API with new client
     *
     * @param array $config (optional,default:[])
     * @param int $version (optional,default:2)
     * @param Guzzle $http
     */
    public function __construct(array $config = [], int $version = 2, $http = null)
    {
        $this->setConfig($config);

        $this->prefix = "api/v$version/";
        $this->http = !isset($http) ? new Guzzle(['base_uri' => $this->host]) : $http;
    }

    /**
     * Set http request config
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $defaults = [];
        $this->config = array_merge($defaults, $config);
    }

    /**
     * Endpoint GET
     *
     * @param string $endpoint
     * @param bool $auth (optional,default:true)
     * @return array
     */
    public function get(string $endpoint, bool $auth = true): array
    {
        return $this->query($endpoint, [], 'get', $auth);
    }

    /**
     * Endpoint POST
     *
     * @param string $endpoint
     * @param array $body (optional,default:[])
     * @return array
     */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->query($endpoint, $body, 'post', true);
    }

    /**
     * Query the endpoint
     *
     * @param string $endpoint
     * @param array $body (optional,default:[])
     * @param string $method (optional,default:'get')
     * @param bool $auth (optional,default:true)
     * @return array
     * @throws GuzzleException
     */
    private function query(string $endpoint, ?array $body = [], string $method = 'get', bool $auth = true): array
    {
        if ($auth && $this->isAuthExpired()) {
            $this->auth();
        }

        $json = [];
        if ($body) {
            $json = [\GuzzleHttp\RequestOptions::JSON => $body];
        }

        $response = $this->http->request($method, $this->prefix . $endpoint, array_merge([
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Authorization' => "Bearer $this->token"
            ]
        ], $json));

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);

        return $data;
    }

    /**
     * Authorize endpoint
     *
     * @return void
     * @throws GuzzleException
     */
    private function auth(): void
    {
        if ($this->token) {
            $this->revoke();
        }

        $params = ['grant_type' => 'password', 'scope' => ''];
        $response = $this->http->request('post', 'oauth/token', [
            \GuzzleHttp\RequestOptions::FORM_PARAMS => array_merge($params, $this->config)
        ]);

        $content = $response->getBody()->getContents();
        $now = time();

        $this->auth = json_decode($content, true);
        $this->token = $this->auth['access_token'];
        $this->expiresAt = $now + $this->auth['expires_in'];
    }

    /**
     * Reset & revoke authorization
     *
     * @return void
     * @throws GuzzleException
     */
    public function revoke(): void
    {
        $this->http->request('post', $this->prefix . 'auth/revoke', [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Authorization' => "Bearer $this->token"
            ]
        ]);

        $this->token = null;
        $this->expiresAt = null;
        $this->auth = [];
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
     * Retrieve auth token
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Sets API auth token
     *
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Retrieves token expiry time
     *
     * @return int|null
     */
    public function getTokenExpiry(): ?int
    {
        return $this->expiresAt;
    }

    /**
     * Retrieves Guzzle implementation
     *
     * @return Guzzle
     */
    public function getHttpClient(): Guzzle
    {
        return $this->http;
    }
}
