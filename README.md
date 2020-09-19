# Netcoins SDK (PHP)

This dev kit aims to offer easy to use, highly configurable access to the Netcoins Inc API.

## Installation

Use [composer](https://getcomposer.org/download/) to install the library.

```bash
composer require netcoins/netcoins-sdk-php
```

## Usage

**Netcoins SDK has a minimum requirement of PHP 7.3.**

Currently most endpoints require authentication. You can authenticate by configuring your Netcoins login credentials and passing them to the Netcoins Client.

Please [contact our developers](#) for full access to the Netcoins API. We will provide you with your auth `token`.

```php
use Netcoins/Client as Netcoins;

$netcoins = new Netcoins([
    'token' => 'your_given_auth_token'
]);

...
```

**We currently support 14 tradeable pairs.**

To fetch a list of available trade pairs:

```php
...

$assets = $netcoins->assets();

print_r($assets);

// outputs:
/*
array(14) {
  ["BTC:CAD"] => string(26) "Bitcoin - Canadian Dollars"
  ["LTC:USD"] => string(27) "Litecoin - American Dollars"
  ...
}
*/
```

To fetch a list of ticker prices:

```php
...

$prices = $netcoins->prices();
// or
$prices = $netcoins->prices('btc', 'cad');

// outputs:
/*
array(14) {
  ["BTC:CAD"]=>
  array(3) {
    ["buy"]=>
    string(8) "13639.86"
    ["sell"]=>
    string(8) "13474.29"
    ["date"]=>
    string(19) "2020-09-11 07:27:03"
  }
  ...
}
*/
```

**Please [consult our wiki](wiki) for comprehensive documentation.**

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

This project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## License
[MIT](https://choosealicense.com/licenses/mit/)
