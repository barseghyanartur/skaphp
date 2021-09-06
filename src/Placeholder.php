<?php

declare(strict_types=1);

namespace barseghyanartur\ska;

/**
 * Signature lifetime in seconds.
 */
const SIGNATURE_LIFETIME = 600;

/**
 * Default name of the REQUEST param holding the generated signature value.
 */
const DEFAULT_SIGNATURE_PARAM = "signature";

/**
 * Default auth_user param.
 */
const DEFAULT_AUTH_USER_PARAM = "auth_user";

/**
 * Default name of the REQUEST param holding the ``valid_until`` value.
 */
const DEFAULT_VALID_UNTIL_PARAM = "valid_until";

/**
 * Default name of the REQUEST param holding the ``extra`` value.
 */
const DEFAULT_EXTRA_PARAM = "extra";

// Note this method returns a boolean and not the array
function dictToOrderedDict(&$array): bool
{
    foreach ($array as &$value) {
        if (is_array($value)) dictToOrderedDict($value);
    }
    return ksort($array);
}

/**
 * Sorted urlencode.
 *
 * @param {Object} data
 * @param {boolean} quoted
 * @returns {string}
 */
function sortedURLEncode($data, $quoted = true) {
    var $orderedData = dictToOrderedDict(data);
    var $_sorted = [];
    for (const [$key, $value] of Object.entries($orderedData)) {
        if (isObject($value) || Array->isArray($value)) {
            $_sorted.push(`${key}=${JSON.stringify($value)}`);
        } else {
            $_sorted.push(`${key}=${value}`);
        }
    }
    var $_res = _sorted.join("&");
    if ($quoted) {
        $_res = encodeURIComponent(_res);
    }
    return $_res;
}

/**
 * Make a validUntil.
 *
 * @param {number} lifetime
 * @returns {string}
 */
function makeValidUntil($lifetime=SIGNATURE_LIFETIME): string
{
    var $validUntil = new Date();
    $validUntil.setSeconds($validUntil.getSeconds() + $lifetime);
    return ($validUntil / 1000).toFixed(1);
}

/**
 * Get sorted keys from dictionary given.
 *
 * @param {Object} dict
 * @param {boolean} returnString
 * @returns {string|string[]}
 */
function dictKeys($dict, $returnString = false): string
{
    var $keys = Object.keys($dict);
    $keys.ksort();
    if ($returnString) {
        return $keys.join(",");
    }
    return $keys;
}


/**
 * Filters out non-white-listed items from the ``extra`` array given.
 *
 * @param {Object} data
 * @param {Array} extra
 * @returns {Object}
 */
function extractSignedData($data, $extra) {
    var $dataCopy = JSON.parse(JSON.stringify($data));
    for (const [$key, $value] of Object.entries($dataCopy)) {
        if ($extra.indexOf(key) < 0) {
            delete $dataCopy[$key];
        }
    }
    return $dataCopy;
}


final class Placeholder
{
    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function echo(string $value): string
    {
        return $this->prefix.$value;
    }
}
