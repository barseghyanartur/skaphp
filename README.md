# ska

Lets you easily sign data, using symmetric-key algorithm encryption. Allows
you to validate signed data and identify possible validation errors. Uses
sha/hmac for signature encryption. Comes with shortcut functions for signing (and
validating) dictionaries and URLs.

## Key concepts

Hosts, that communicate with each other, share the Secret Key, which is used
to sign data (requests). Secret key is never sent around.

One of the cases is signing of HTTP requests. Each (HTTP) request is signed
on the sender side using the shared Secret Key and as an outcome produces the
triple (`signature`, `auth_user`, `valid_until`) which are used to sign
the requests.

-   `signature` (`string`): Signature generated.
-   `auth_user` (`string`): User making the request. Can be anything.
-   `valid_until` (`float` or `string`): Signature expiration time (Unix timestamp).

On the recipient side, (HTTP request) data is validated using the shared
Secret Key. It's being checked whether signature is valid and not expired.

```
    ┌─────────────┐           Data              ┌─────────────┐
    │   Host 1    ├────────────────────────────>│   Host 2    │
    │ ─────────── │                             │ ─────────── │
    │ secret key  │                             │ secret key  │
    │ 'my-secret' │<────────────────────────────┤ 'my-secret' │
    └─────────────┘           Data              └─────────────┘
```

## Features

-   Sign dictionaries.
-   Validate signed dictionaries.
-   Sign URLs. Append and sign additional URL data.
-   Validate URLs.

## Installation

Latest stable version from composer registry:

```shell
composer require barseghyanartur/ska
```

## Usage examples

Usage example are present.

```shell
php examples/kitchen_sink.php
```

### Basic usage

#### Sender side

Signing dictionaries is as simple as follows.

##### Required imports.

```php
require_once(dirname(__FILE__)."/src/SKA/core.php");
use SKA;
```

##### Sign data

**Sample usage:**

```php
$signedData = SKA\signatureToDict("user", "your-secret_key");
print_r($signedData);
```

**Sample output:**

```php
Array
(
    [signature] => WEwnd40jMusHD6hRZ9WOCR8Zym4=
    [auth_user] => user
    [valid_until] => 1631795130.0
    [extra] => 
)
```

**Adding of additional data to the signature works in the same way:**

```php
$signedData = SKA\signatureToDict(
    "user", 
    "your-secret_key", 
    [
        "email" => "john.doe@mail.example.com",
        "first_name" => "John",
        "last_name" => "Doe",
    ]
);
print_r($signedData);
```

**Sample output:**

```php
Array
(
    [signature] => B0sscS+xXWU+NR+9dBCoGFnDtlw=
    [auth_user] => user
    [valid_until] => 1631797926.0
    [extra] => email,first_name,last_name
    [email] => john.doe@mail.example.com
    [first_name] => John
    [last_name] => Doe
)
```

**Options and defaults:**

The `signatureToDict` function accepts an optional `$options` argument.

Default value for the `validUntil` in the `$options` is 10 minutes from now. If
you want it to be different, set `validUntil` in the `$options` of 
the `signatureToDict` function.

Default lifetime of a signature is 10 minutes (600 seconds). If you want it
to be different, set `lifetime` in the `$options` of the `signatureToDict` function.

Default name of the (GET) param holding the generated signature value
is `signature`. If you want it to be different, set the `signatureParam`
in the `$options` of the `signatureToDict` function.

Default name of the (GET) param holding the `authUser` value is
`auth_user`. If you want it to be different, set `authUserParam`
in the `$options` of the `signatureToDict` function.

Default name of the (GET) param holding the `validUntil` value is
`valid_until`. If you want it to be different, set the `validUntilParam`
in the `$options` of the `signatureToDict` function.

```php
$signedData = SKA\signatureToDict(
    "user", 
    "your-secret_key", 
    [
        "email" => "john.doe@mail.example.com",
        "first_name" => "John",
        "last_name" => "Doe",
    ],
    [
        "authUserParam" => "webshop_id"  
    ]
)
print_r($signedData);
```

**Sample output:**

```php
Array
(
    [signature] => nu0Un+05z/cNOFnLwQnigoW/KmA=
    [webshop_id] => user
    [valid_until] => 1631799172.0
    [extra] => email,first_name,last_name
    [email] => john.doe@mail.example.com
    [first_name] => John
    [last_name] => Doe
)
```

#### Recipient side

Validating the signed request data is as simple as follows.

##### Validate signed requests

Validating the signed request data. Note, that `$data` value is expected to
be a dictionary; `request.GET` is given as an example. It will most likely
vary from what's used in your framework (unless you use Django).

```php
$validationResult = SKA\validateSignedRequestData(
    request.GET, // Note, that ``request.GET`` is given as example.
    "your-secret_key"
);
```

# Testing

Simply type:

```shell
composer test
```

# Code style

The `Prettier` is used.

```shell
npx prettier --write .
```

# License

MIT

# Support

For any issues contact me at the e-mail given in the [Author](#Author) section.

# Author

Artur Barseghyan <artur.barseghyan@gmail.com>
