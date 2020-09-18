<?php
namespace Netcoins\Auth;

use GuzzleHttp\Exception\GuzzleException;

/**
 * Client credentials auth type for Netcoins API
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
class AuthClientCredentials extends Auth
{
    /**
     * Authorizes API access
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function authorize(): void
    {
        if ($this->getToken()) {
            // if we already have an auth token and try to authorize, revoke the existing token
            $this->revoke();
        }

        $params = ['grant_type' => 'password', 'scope' => ''];
        $response = $this->http->request('post', 'oauth/token', [
            \GuzzleHttp\RequestOptions::FORM_PARAMS => array_merge($params, $this->config)
        ]);

        $content = $response->getBody()->getContents();
        $now = time();

        $this->response = json_decode($content, true);
        $this->token = $this->response['access_token'];
        $this->expiresAt = $now + $this->response['expires_in'];
    }
}
