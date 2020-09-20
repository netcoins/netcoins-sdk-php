<?php
namespace Netcoins\Contracts;

use GuzzleHttp\Exception\GuzzleException;

/**
 * An interface to determine the obligations of a concrete Authorization class.
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
interface AuthInterface
{
    /**
     * Authorizes API access.
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function authorize(): void;

    /**
     * Resets & revokes authorization.
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function revoke(): void;

    /**
     * Gets auth config.
     *
     * @return array
     */
    public function getConfig(): array;

    /**
     * Has auth expired?
     *
     * @return bool
     */
    public function isAuthExpired(): bool;

    /**
     * Gets auth token.
     *
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * Gets token expiry time.
     *
     * @return int|null
     */
    public function getTokenExpiry(): ?int;
}
