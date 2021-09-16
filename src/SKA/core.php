<?php
declare(strict_types=1);


namespace SKA;

use DateTime;
use Exception;

/**
 * *******************************************
 * *************** Constants *****************
 * *******************************************
 */

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

/**
 * *******************************************
 * *************** Helpers *****************
 * *******************************************
 */

/**
 * Encode URL components.
 *
 * @param string $str
 * @return string
 */
function encodeURIComponent(string $str): string
{
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

/**
 * Dict to ordered dict.
 *
 * @param array $dict
 * @return array
 */
function dictToOrderedDict(array $dict): array
{
    $dictCopy = unserialize(serialize($dict));
    ksort($dictCopy);
    foreach ($dictCopy as $key => $value) {
        if (is_array($value)) {
            $dictCopy[$key] = dictToOrderedDict($value);
        }
    }
    return $dictCopy;
}

/**
 * Sorted urlencode.
 *
 * @param array $data
 * @param bool $quoted
 * @return string
 */
function sortedURLEncode(array $data, bool $quoted = true): string
{
    $orderedData = dictToOrderedDict($data);
    $_sorted = [];
    foreach ($orderedData as $key => $value) {
        if (is_array($value)) {
            $_sorted[] = $key.'='.json_encode($value, JSON_UNESCAPED_SLASHES);
        } else {
            $_sorted[] = $key.'='.$value;
        }
    }
    $_res = implode("&", $_sorted);
    if ($quoted) {
        $_res = encodeURIComponent($_res);
    }
    return $_res;
}

/**
 * Format value of validUntil.
 *
 * @param string|int|float $validUntil
 * @return string
 */
function formatValidUntil($validUntil): string {
    if (is_string($validUntil)) {
        return $validUntil;
    }
    return number_format($validUntil, 1, '.', '');
}


/**
 * Make a validUntil.
 *
 * @param int $lifetime
 * @return string
 */
function makeValidUntil(int $lifetime = SIGNATURE_LIFETIME): string {
    $validUntil = time() + $lifetime;
    return formatValidUntil($validUntil);
}

/**
 * Get sorted keys from dictionary given.
 *
 * @param array dict
 * @param bool returnString
 * @return string|string[]
 */
function dictKeys(array $dict, bool $returnString = false)
{
    $keys = array_keys($dict);
    sort($keys);
    if ($returnString) {
        return implode(",", $keys);
    }
    return $keys;
}

/**
 * Filters out non-white-listed items from the ``extra`` array given.
 *
 * @param array $data
 * @param array $extra
 * @return array
 */
function extractSignedData(array $data, array $extra): array {
    $dataCopy = unserialize(serialize($data));
    foreach ($dataCopy as $key => $value) {
        if (!in_array($key, $extra, true)) {
            unset($dataCopy[$key]);
        }
    }
    return $dataCopy;
}

/**
 * *******************************************
 * ****************** Base *******************
 * *******************************************
 */

/**
 * Signature.
 */
class Signature {
    public string $signature;
    public string $authUser;
    public string $validUntil;
    public array $extra;

    /**
     * Constructor.
     *
     * @param string signature
     * @param string authUser
     * @param string|int|float validUntil
     * @param array extra
     */
    public function __construct(
        string $signature,
        string $authUser,
        string $validUntil,
        array $extra
    ) {
        $this->signature = $signature;
        $this->authUser = $authUser;
        $this->validUntil = $validUntil;
        $this->extra = $extra;
    }

    /**
     * Check if signature is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $now = new DateTime();
        $validUntil = unixTimestampToDate($this->validUntil);
        $res = $validUntil > $now;
        return !$res;
    }
}

/**
 * Validate signature.
 *
 * @param string $signature
 * @param string $authUser
 * @param string $secretKey
 * @param string|int|float $validUntil
 * @param array|null $extra
 * @param bool $returnObject
 * @return bool
 */
function validateSignature(
    string $signature,
    string $authUser,
    string $secretKey,
    $validUntil,
    array $extra = null,
    bool $returnObject = false
): bool {
    if (!$extra) {
        $extra = array();
    }

    $sig = generateSignature(
        $authUser,
        $secretKey,
        $validUntil,
        SIGNATURE_LIFETIME,
        $extra,
    );

    if (!$returnObject) {
        return $sig->signature === $signature && !$sig->isExpired();
    }
}

/**
 * *******************************************
 * ****************** Utils ******************
 * *******************************************
 */

/**
 * Request helper.
 */
class RequestHelper {
    /**
     * @var string
     */
    public string $signatureParam;
    /**
     * @var string
     */
    public string $authUserParam;
    /**
     * @var string
     */
    public string $validUntilParam;
    /**
     * @var string
     */
    public string $extraParam;

    /**
     * Constructor.
     *
     * @param string $signatureParam
     * @param string $authUserParam
     * @param string $validUntilParam
     * @param string $extraParam
     */
    public function __construct(
        string $signatureParam = DEFAULT_SIGNATURE_PARAM,
        string $authUserParam = DEFAULT_AUTH_USER_PARAM,
        string $validUntilParam = DEFAULT_VALID_UNTIL_PARAM,
        string $extraParam = DEFAULT_EXTRA_PARAM
    ) {
        $this->signatureParam = $signatureParam;
        $this->authUserParam = $authUserParam;
        $this->validUntilParam = $validUntilParam;
        $this->extraParam = $extraParam;
    }

    /**
     * Signature to dict.
     *
     * @param Signature $signature
     * @return array
     */
    public function signatureToDict(Signature $signature): array
    {
        $data = array();

        $data[$this->signatureParam] = $signature->signature;
        $data[$this->authUserParam] = $signature->authUser;
        $data[$this->validUntilParam] = $signature->validUntil;
        $data[$this->extraParam] = dictKeys($signature->extra, true);

        return array_merge($data, $signature->extra);
    }

    /**
     * Validate request data.
     *
     * @param array data
     * @param string secretKey
     */
    public function validateRequestData($data, $secretKey): bool
    {
        $signature = $data[$this->signatureParam];
        $authUser = $data[$this->authUserParam];
        $validUntil = $data[$this->validUntilParam];
        $_extra = $data[$this->extraParam];
        $extraData = array();
        if ($_extra) {
            $_extra = explode(",", $_extra);
            $extraData = extractSignedData($data, $_extra);
        }

        return validateSignature(
            $signature,
            $authUser,
            $secretKey,
            $validUntil,
            $extraData,
        );
    }
}

/**
 * *******************************************
 * ************* Borrowed from classes *******
 * *******************************************
 */

/**
 * @param string|int|float $timestamp
 * @return string
 */
function normalizeUnixTimestamp($timestamp): string
{
    return sprintf("%01.1f", $timestamp);
}

/**
 * Convert unix timestamp to date.
 *
 * @param string|int|float $validUntil
 * @return DateTime|false
 */
function unixTimestampToDate($validUntil)
{
    return DateTime::createFromFormat('U.u', normalizeUnixTimestamp($validUntil));
}

/**
 * Make a secret key.
 *
 * @param string $authUser
 * @param string|int|float $validUntil
 * @param array|null $extra
 * @return string
 */
function getBase(string $authUser, $validUntil, array $extra = null) {
    if (!$extra) {
        $extra = array();
    }

    $validUntil = normalizeUnixTimestamp($validUntil);

    $_base = [$validUntil, $authUser];

    if ($extra) {
        $urlencodedExtra = sortedURLEncode($extra);
        if ($urlencodedExtra) {
            $_base[] = $urlencodedExtra;
        }
    }

    return implode("_", $_base);
}

/**
 * Make hash.
 *
 * @param string $authUser
 * @param string $secretKey
 * @param string|int|float $validUntil
 * @param array|null extra
 * @return string
 */
function makeHash(
    string $authUser,
    string $secretKey,
    $validUntil = null,
    array $extra = null
): string
{
    if (is_null($extra)) {
        $extra = array();
    }

    $_base = getBase($authUser, $validUntil, $extra);
    return hash_hmac("sha1", $_base, $secretKey, true);
}

/**
 * Generate signature.
 *
 * @param string $authUser
 * @param string $secretKey
 * @param string|int|float $validUntil
 * @param int $lifetime
 * @param array|null $extra
 * @return null|Signature
 */
function generateSignature(
    string $authUser,
    string $secretKey,
    $validUntil = null,
    int $lifetime = SIGNATURE_LIFETIME,
    array $extra = null
): ?Signature
{
    if (is_null($extra)) {
        $extra = array();
    }

    if (is_null($validUntil)) {
        $validUntil = makeValidUntil($lifetime);
    } else {
        try {
            unixTimestampToDate($validUntil);
        } catch (Exception $err) {
            return null;
        }
    }
    $validUntil = formatValidUntil($validUntil);

    $hash = makeHash($authUser, $secretKey, $validUntil, $extra);
    $signature = base64_encode($hash);

    return new Signature($signature, $authUser, $validUntil, $extra);
}

/**
 * Get defaults for signatureToDict function.
 *
 * @param int|null $lifetime
 * @return array
 */
function getSignatureToDictDefaults(int $lifetime = null): array
{
    // * @param string|int|float|null validUntil
    // * @param int|null lifetime
    // * @param string signatureParam
    // * @param string authUserParam
    // * @param string validUntilParam
    // * @param string extraParam
    if (is_null($lifetime)) {
        $lifetime = SIGNATURE_LIFETIME;
    }
    return [
        "validUntil" => makeValidUntil($lifetime),
        "lifetime" => $lifetime,
        "signatureParam" => DEFAULT_SIGNATURE_PARAM,
        "authUserParam" => DEFAULT_AUTH_USER_PARAM,
        "validUntilParam" => DEFAULT_VALID_UNTIL_PARAM,
        "extraParam" => DEFAULT_EXTRA_PARAM
    ];
}

/**
 * Signature to dict.
 *
 * @param string $authUser
 * @param string $secretKey
 * @param array|null $extra
 * @param array|null $options
 * @return array
 */
function signatureToDict(
    string $authUser,
    string $secretKey,
    array $extra = [],
    array $options = []
): array
{
    $lifetime = $options["lifetime"] ?? SIGNATURE_LIFETIME;
    $defaults = getSignatureToDictDefaults($lifetime);
    $options = array_replace($defaults, $options);
    $validUntil = $options["validUntil"];
//    $lifetime = $options["lifetime"];
    $signatureParam = $options["signatureParam"];
    $authUserParam = $options["authUserParam"];
    $validUntilParam = $options["validUntilParam"];
    $extraParam = $options["extraParam"];

    $signature = generateSignature(
        $authUser,
        $secretKey,
        $validUntil,
        $lifetime,
        $extra,
    );

    $requestHelper = new RequestHelper(
        $signatureParam,
        $authUserParam,
        $validUntilParam,
        $extraParam,
    );

    return $requestHelper->signatureToDict($signature);
}

/**
 * Validate signed request data.
 *
 * @param array $data
 * @param string $secretKey
 * @param string $signatureParam
 * @param string $authUserParam
 * @param string $validUntilParam
 * @param string $extraParam
 * @param bool $validate
 * @param bool $failSilently
 * @return bool
 */
function validateSignedRequestData(
    array $data,
    string $secretKey,
    string $signatureParam = DEFAULT_SIGNATURE_PARAM,
    string $authUserParam = DEFAULT_AUTH_USER_PARAM,
    string $validUntilParam = DEFAULT_VALID_UNTIL_PARAM,
    string $extraParam = DEFAULT_EXTRA_PARAM,
    bool $validate = false,
    bool $failSilently = false
) {
    $requestHelper = new RequestHelper(
        $signatureParam,
        $authUserParam,
        $validUntilParam,
        $extraParam
    );

    return $requestHelper->validateRequestData($data, $secretKey);
}
