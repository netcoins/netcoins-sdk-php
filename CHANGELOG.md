# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
- Better exception handling. Creating new Exception classes.
- Updating limit methods once API server correctly accepts params & responds.

## [0.1.1] - 2020-09-17
### Added
- Abstract Auth class created to handle authorization to Netcoins API.
- AuthClientCredentials created to handle legacy `client_id` and `client_secret` connection.
- AuthPersonalAccessToken created and set as default. Consumers are given a long lived `token` to connect.
### Changed
- Auth abstracted from Connector.

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
