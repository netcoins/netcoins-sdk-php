<?php
namespace Netcoins\Contracts;

use GuzzleHttp\Client as Guzzle;

/**
 * An interface to support some simple API HTTP methods.
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
interface ApiInterface
{
    /**
     * Endpoint GET
     *
     * @param string $endpoint
     * @param bool $auth
     * @return array
     */
    public function get(string $endpoint, bool $auth = true) : array;

    /**
     * Endpoint POST
     *
     * @param string $endpoint
     * @param array $body
     * @return array
     */
    public function post(string $endpoint, array $body = []) : array;

    /**
     * Retrieves Guzzle implementation
     *
     * @return Guzzle
     */
    public function getHttpClient() : Guzzle;
}
