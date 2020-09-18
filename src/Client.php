<?php
namespace Netcoins;

use Netcoins\Connector;
use Netcoins\Contracts\ApiInterface;
use Netcoins\Exceptions\AssetNotAvailableException;
use Netcoins\Exceptions\InvalidAttributeException;

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
    const CURRENCIES = ['cad', 'usd'];

    /**
     * @var array
     */
    const STATUSES = ['open', 'cancelled', 'delivered'];

    /**
     * Accepts a config array of connection details, and an optional version constraint.
     * Additionally can be given an new API Connector if you intend to write your own.
     *
     * @param array         $config (optional,default:[])
     * @param int           $version (optional,default:2)
     * @param ApiInterface  $http
     */
    public function __construct($config = [], $version = 2, ApiInterface $api = null)
    {
        $this->api = !isset($api) ? new Connector($config, $version) : $api;
    }

    /**
     * Fetches the price for all tradeable pairs, or a given asset/pair
     *
     * @param string    $asset (optional)
     * @param string    $currency (optional)
     *
     * @return array
     */
    public function prices(?string $asset = null, ?string $currency = null): array
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
     * Fetches a list of tradeable assets
     *
     * @return array
     */
    public function assets(): array
    {
        return $this->api->get('assets');
    }

    /**
     * Fetches a quote for a given asset/quantity
     *
     * @param float     $value
     * @param string    $side
     * @param string    $asset
     * @param string    $currency
     * @param bool      $useFiat (optional,default:false)
     *
     * @return array
     *
     * @throws InvalidAttributeException
     */
    public function quote(float $value, string $side, string $asset, string $currency, bool $useFiat = false): array
    {
        // check currency exists in allowed currencies array
        if (!in_array(strtolower($currency), static::CURRENCIES)) {
            throw new InvalidAttributeException('tradeable pair not valid. You may only trade against
                the following currencies: ' . implode(', ', static::CURRENCIES));
        }

        $values = [];
        if (!$useFiat) {
            $values['quantity'] = $value;
        }

        if ($useFiat) {
            $values['amount'] = $value;
        }

        return $this->api->post('quote', array_merge($values, [
            'side' => $side,
            'asset' => strtolower($asset),
            'counter_asset' => strtolower($currency),
        ]));
    }

    /**
     * Creates an order for a given asset/pair
     *
     * @param string    $quoteId
     *
     * @return array
     */
    public function execute(string $quoteId): array
    {
        return $this->api->post('execute', ['quote_id' => $quoteId]);
    }

    /**
     * Creates a withdraw request for a given asset quantity
     *
     * @param string    $asset
     * @param float     $quantity
     * @param string    $wallet
     * @param string    $memo (optional)
     *
     * @return array
     */
    public function withdraw(string $asset, float $quantity, string $wallet, ?string $memo = null): array
    {
        return $this->api->post('withdraw', [
            'asset' => strtolower($asset),
            'quantity' => $quantity,
            'address' => $wallet,
            'memo' => $memo,
        ]);
    }

    /**
     * Opens a buy limit order
     *
     * @param float     $price
     * @param float     $amount
     * @param string    $asset
     * @param string    $currency
     *
     * @return array
     *
     * @throws InvalidAttributeException
     */
    public function limitBuy(float $price, float $amount, string $asset, string $currency): array
    {
        // check currency exists in allowed currencies array
        if (!in_array(strtolower($currency), static::CURRENCIES)) {
            throw new InvalidAttributeException('tradeable pair not valid. You may only trade against
                the following currencies: ' . implode(', ', static::CURRENCIES));
        }

        return $this->api->post('order', [
            'price' => $price,
            'amount' => $amount,
            'side' => 'buy',
            'asset' => $asset,
            'counter_asset' => $currency,
        ]);
    }

    /**
     * Opens a sell limit order
     *
     * @param float     $price
     * @param float     $quantity
     * @param string    $asset
     * @param string    $currency
     *
     * @return array
     *
     * @throws InvalidAttributeException
     */
    public function limitSell(float $price, float $quantity, string $asset, string $currency): array
    {
        // check currency exists in allowed currencies array
        if (!in_array(strtolower($currency), static::CURRENCIES)) {
            throw new InvalidAttributeException('tradeable pair not valid. You may only trade against
                the following currencies: ' . implode(', ', static::CURRENCIES));
        }

        return $this->api->post('order', [
            'price' => $price,
            'quantity' => $quantity,
            'side' => 'sell',
            'asset' => $asset,
            'counter_asset' => $currency,
        ]);
    }

    /**
     * Cancels a limit order
     *
     * @param string $orderId
     *
     * @return array
     */
    public function limitCancel(string $orderId): array
    {
        return $this->api->post('order/cancel', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Fetches a list of open limit orders
     *
     * @param int       $beforeDateTime (optional)
     * @param int       $afterDateTime (optional)
     * @param int       $offset (optional)
     * @param int       $limit (optional)
     * @param string    $status (optional)
     *
     * @return array
     *
     * @throws InvalidAttributeException
     *
     */
    public function orders(?int $beforeDateTime = null, ?int $afterDateTime = null,
        ?int $offset = null, ?int $limit = null, ?string $status = null): array
    {
        $params = [];

        // convert timestamp to formatted date time
        if (!empty($beforeDateTime)) {
            $beforeDateTime = date('Y-m-d H:i:s', $beforeDateTime);
            $params['before'] = $beforeDateTime;
        }

        // convert timestamp to formatted date time
        if (!empty($afterDateTime)) {
            $afterDateTime = date('Y-m-d H:i:s', $afterDateTime);
            $params['after'] = $afterDateTime;
        }

        if (!empty($status)) {
            $params['status'] = $status;
        }

        // check status exists in allowed status array
        if (!empty($status) && !in_array(strtolower($status), static::STATUSES)) {
            throw new InvalidAttributeException('Status \''.$status.'\' is not a valid limit order status.
                Please use one of the following, or omit the status:' . implode(', ', static::STATUSES));
        }

        if (!empty($offset)) {
            $params['offset'] = $offset;
        }

        if (!empty($limit)) {
            $params['limit'] = $limit;
        }

        return $this->api->get('orders', true, $params);
    }

    /**
     * Fetches account details for the authorized user
     *
     * @return array
     */
    public function account(): array
    {
        return $this->api->get('account');
    }

    /**
     * Fetches a list of account balances
     *
     * @param string    $asset (optional)
     *
     * @return array
     */
    public function balances(?string $asset = null): array
    {
        $balances = $this->api->get('balances');
        $asset = strtoupper($asset);

        if ($asset && isset($balances[$asset])) {
            return [
                "$asset" => $balances[$asset],
            ];
        }

        return $balances;
    }

    /**
     * Fetches the balance of a single asset
     *
     * @param string    $asset
     *
     * @return float
     */
    public function balance(string $asset): float
    {
        $balances = $this->api->get('balances');
        $asset = strtoupper($asset);

        if (isset($balances[$asset])) {
            return (float) $balances[$asset];
        }

        return (float) 0.0;
    }

    /**
     * Fetches your Netcoins deposit address for a given asset
     *
     * @param string    $asset
     *
     * @return string|null
     */
    public function depositAddress(string $asset): ?string
    {
        $asset = strtoupper($asset);
        $address = $this->api->get('deposit/'.$asset);

        if (isset($address['deposit_address'])) {
            return $address['deposit_address'];
        }

        return null;
    }

    /**
     * Fetch withdrawal fee for each asset
     *
     * @return array
     */
    public function fees(): array
    {
        return $this->api->get('fees', true);
    }

    /**
     * Fetches the withdrawal fee for a single asset
     *
     * @param string    $asset
     *
     * @return float
     *
     * @throws AssetNotAvailableException
     */
    public function fee(string $asset): float
    {
        $fees = $this->api->get('fees', true);
        $asset = strtoupper($asset);

        if (!isset($fees[$asset])) {
            throw new AssetNotAvailableException('Asset \''.$asset.'\' is unavailable or does not exist.');
        }

        return (float) $fees[$asset];
    }

    /**
     * Fetch the mins and maxes for buy and sell of each asset
     *
     * @return array
     */
    public function boundaries(): array
    {
        return $this->api->get('boundaries', true);
    }

    /**
     * Fetch the mins and maxes for buy and sell of a single asset
     *
     * @param string $asset
     *
     * @return array
     *
     * @throws AssetNotAvailableException
     */
    public function boundary(string $asset): array
    {
        $boundaries = $this->api->get('boundaries', true);
        $asset = strtoupper($asset);

        if (!isset($boundaries[$asset])) {
            throw new AssetNotAvailableException('Asset \''.$asset.'\' is unavailable or does not exist.');
        }

        return $boundaries[$asset];
    }

    /**
     * Return instance of Netcoins API Connector
     *
     * @return ApiInterface
     */
    public function getAPIConnector(): ApiInterface
    {
        return $this->api;
    }
}
