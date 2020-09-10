<?php
namespace Netcoins;

use Netcoins\Contracts\ApiInterface;
use Netcoins\Connector;

/**
 * The netcoin API client. A wrapper around the API connector
 * for verbose method names and response formatting.
 *
 * @author Simon Willan <swillan@gonetcoins.com>
 */
class Client
{
    /**
     * @var ApiInterface
     */
    protected $api;

    /**
     * @var array
     */
    protected $currencies = ['cad', 'usd'];

    /**
     * Accepts a config array of connection details, and an optional version constraint.
     * Additionally can be given an new API Connector if you intend to write your own.
     *
     * @param array $config (optional,default:[])
     * @param int $version (optional,default:2)
     * @param ApiInterface $http
     */
    public function __construct($config = [], $version = 2, ApiInterface $api = null)
    {
        $this->api = !isset($api) ? new Connector($config, $version) : $api;
    }

    /**
     * Fetch the price for all tradeable pairs, or a given asset/pair
     *
     * @param string $asset (optional)
     * @param string $currency (optional)
     * @return array
     */
    public function prices(?string $asset = null, ?string $currency = null) : array
    {
        $prices = $this->api->get('prices');

        if ($asset && $currency) {
            $asset = strtoupper($asset);
            $currency = strtoupper($currency);

            return $prices["$asset:$currency"];
        }

        return $prices;
    }

    /**
     * Fetch a list of tradeable assets
     *
     * @return array
     */
    public function assets() : array
    {
        return $this->api->get('assets');
    }

    /**
     * Fetch a quote for a given asset/quantity
     *
     * @param float $quantity
     * @param string $side
     * @param string $asset
     * @param string $currency
     * @throws \Exception
     * @return array
     */
    public function quote(float $quantity, string $side, string $asset, string $currency) : array
    {
        if (!in_array(strtolower($currency), $this->currencies)) {
            throw new \Exception('tradeable pair not valid. You may only trade against the following currencies: ' . implode(', ', $this->currencies));
        }

        return $this->api->post('quote', [
            'quantity' => $quantity,
            'side' => $side,
            'asset' => strtolower($asset),
            'counter_asset' => strtolower($currency),
        ]);
    }

    /**
     * Create order for a given asset/pair
     *
     * @param string $quoteId
     * @return array
     */
    public function execute(string $quoteId) : array
    {
        return $this->api->post('execute', ['quote_id' => $quoteId]);
    }

    /**
     * Create a withdraw request for a given asset quantity
     *
     * @param string $asset
     * @param float $quantity
     * @param string $wallet
     * @param string $memo (optional)
     * @return array
     */
    public function withdraw(string $asset, float $quantity, string $wallet, ?string $memo = null) : array
    {
        return $this->api->post('withdraw', [
            'asset' => strtolower($asset),
            'quantity' => $quantity,
            'address' => $wallet,
            'memo' => $memo,
        ]);
    }

    /**
     * Convert fiat amount to crypto quantity
     *
     * @param float $fiat
     * @param string $side
     * @param string $asset
     * @param string $currency
     * @return float
     */
    public function convert(float $fiat, string $side, string $asset, string $currency) : float
    {
        $minimums = ['btc' => 0.001, 'ltc' => 0.5, 'eth' => 0.1, 'xrp' => 50, 'bch' => 0.1];

        // fetch arbitrary quote for an accurate asset price.
        $quote = $this->quote($minimums[strtolower($asset)], $side, $asset, $currency);
        $price = isset($quote['price']) ? $quote['price'] : 0;

        if (!$price) {
            throw new \Exception('price is empty, quoting is down.');
        }

        return bcdiv($fiat, $price, 8);
    }

    /**
     * Return instance of Netcoins API Connector
     *
     * @return ApiInterface
     */
    public function getAPIConnector() : ApiInterface
    {
        return $this->api;
    }
}
