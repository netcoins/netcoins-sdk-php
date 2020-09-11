# Changelog
All notable changes to this project will be documented in this file.

## [0.1.0] - 2020-09-11
### Added
- Netcoins API Connector. A simple wrapper around Guzzle HTTP.
- Netcoin API Client, with the following methods:
    - public
        - assets()
        - prices()
    - account
        - account()
        - depositAddress()
        - balances()
        - balance()
        - withdraw()
    - trade
        - quote()
        - execute()
        - limitBuy()
        - limitSell()
        - limitCancel()
        - orders()
- PHPUnit tests for Connector and Client classes.
