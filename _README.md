# ska

Sign data using symmetric-key algorithm encryption. 
Validate signed data and identify possible validation errors. 
Uses sha-(1, 224, 256, 385 and 512)/hmac for signature encryption. 
Custom hash algorithms are allowed. Useful shortcut functions for signing (and 
validating) dictionaries and URLs.

## Prerequisites

```shell
sudo dnf install php php-json php-mbstring composer php-xdebug php-pear
```

## Installation

```shell
$ composer require barseghyanartur/ska
```

## Running tests

```shell
$ composer test
```
