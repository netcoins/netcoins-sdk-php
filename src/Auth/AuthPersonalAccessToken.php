<?php
namespace Netcoins\Auth;

use DateInterval;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Personal Access Token auth type for Netcoins API.
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
class AuthPersonalAccessToken extends Auth
{
    /**
     * Authorizes API access.
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function authorize(): void
    {
        $now = new DateTime();
        $expiresAt = $now->add(new DateInterval('P1Y'));

        $this->response = [];
        $this->token = $this->config['token'];
        $this->expiresAt = isset($this->config['expires_at']) ? $this->config['expires_at'] : $expiresAt;
    }
}
