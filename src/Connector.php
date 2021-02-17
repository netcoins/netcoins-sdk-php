<?php
namespace Netcoins;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Netcoins\Auth\AuthPersonalAccessToken;
use Netcoins\Contracts\ApiInterface;
use Netcoins\Contracts\AuthInterface;

/**
 * A class to handle connection to API via guzzle.
 * Handles querying the API.
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
     * @var AuthInterface
     */
    private $auth;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $sandbox = 'https://staging.netcoins.app';

    /**
     * @var string
     */
    private $production = 'https://netcoins.app';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * Setup API with new client.
     *
     * @param array         $config
     * @param int           $version (optional,default:2)
     * @param Guzzle        $http    (optional)
     * @param AuthInterface $auth    (optional)
     */
    public function __construct(array $config, int $version = 2, $http = null, AuthInterface $auth = null)
    {
        $this->setConfig($config);

        $this->prefix = "api/v$version/";
        $this->http = !isset($http) ? new Guzzle(['base_uri' => $this->getHost()]) : $http;
        $this->auth = !isset($auth) ? new AuthPersonalAccessToken($config, $this->prefix, $this->http) : $auth;
    }

    /**
     * Endpoint GET.
     *
     * @param string $endpoint
     * @param bool   $auth     (optional,default:true)
     * @param array  $body     (optional,default:[])
     *
     * @return array
     */
    public function get(string $endpoint, bool $auth = true, array $body = []): array
    {
        return $this->query($endpoint, $body, 'get', $auth);
    }

    /**
     * Endpoint POST.
     *
     * @param string $endpoint
     * @param array  $body     (optional,default:[])
     *
     * @return array
     */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->query($endpoint, $body, 'post', true);
    }

    /**
     * Queries an endpoint.
     *
     * @param string $endpoint
     * @param array  $body     (optional,default:[])
     * @param string $method   (optional,default:'get')
     * @param bool   $auth     (optional,default:true)
     *
     * @return array
     *
     * @throws GuzzleException
     */
    private function query(string $endpoint, ?array $body = [], string $method = 'get', bool $auth = true): array
    {
        $headers = [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
        ];

        if ($auth) {
            if ($this->auth->isAuthExpired()) {
                $this->auth->authorize();
            }

            $headers[\GuzzleHttp\RequestOptions::HEADERS]['Authorization'] = "Bearer {$this->auth->getToken()}";
        }

        $params = [];
        if ($body && in_array(strtolower($method), ['post', 'put', 'patch', 'delete'])) {
            $params = [\GuzzleHttp\RequestOptions::JSON => $body];
        } elseif ($body && 'get' === strtolower($method)) {
            $params = [\GuzzleHttp\RequestOptions::QUERY => $body];
        }

        $response = $this->http->request($method, $this->prefix.$endpoint, array_merge($headers, $params));

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);

        return $data;
    }

    /**
     * Set configuration for connector.
     *
     * @param array $config
     *
     * @return void
     */
    private function setConfig(array $config) : void
    {
        $defaults = ['environment' => 'sandbox'];
        $config = array_intersect_key($config, array_flip(['environment']));
        $config = array_merge($defaults, $config);

        $this->config = $config;
    }

    /**
     * Get config settings for connector.
     *
     * @return array
     */
    public function getConfig() : array
    {
        return $this->config;
    }

    /**
     * Returns hostname based on environment.
     *
     * @return string
     */
    public function getHost() : string
    {
        return strtolower($this->config['environment']) === 'production' ? $this->production : $this->sandbox;
    }

    /**
     * Gets Guzzle implementation.
     *
     * @return Guzzle
     */
    public function getHttpClient(): Guzzle
    {
        return $this->http;
    }

    /**
     * Returns the concrete Auth implementation.
     *
     * @return AuthInterface
     */
    public function getAuthHandler(): AuthInterface
    {
        return $this->auth;
    }
}
