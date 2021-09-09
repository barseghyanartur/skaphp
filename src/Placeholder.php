<?php

declare(strict_types=1);

//namespace barseghyanartur\ska;
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
            $_sorted[] = '"'.$key.'='.json_encode($value).'"';
        } else {
            $_sorted[] = "${key}=${value}";
        }
    }
    $_res = implode("&", $_sorted);
    if ($quoted) {
        $_res = urlencode($_res);
    }
    return $_res;
}

/**
 * Make a validUntil.
 *
 * @param int $lifetime
 * @return string
 */
function makeValidUntil(int $lifetime = SIGNATURE_LIFETIME): string {
    $validUntil = time() + $lifetime;
    return number_format($validUntil, 1, '.', '');
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
    foreach ($dataCopy as $key=> $value) {
        if (in_array($key, $extra)) {
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
    private string $signature;
    private string $authUser;
    private string $validUntil;
    private array $extra;

    /**
     * Constructor.
     *
     * @param string signature
     * @param string authUser
     * @param string|int|float validUntil
     * @param array extra
     */
    public function __construct(string $signature, string $authUser, string $validUntil, array $extra) {
        $this->signature = $signature;
        $this->authUser = $authUser;
        $this->validUntil = $validUntil;
        $this->extra = $extra ? $extra : array();
    }

    /**
     * Check if signature is expired.
     *
     * @return boolean
     */
    public function isExpired() {
        $now = new Date();
        $validUntil = unixTimestampToDate($this->validUntil);
        $res = $validUntil > $now;
        return !$res;
    }
}


/**
 * *******************************************
 * ************* Borrowed from classes *******
 * *******************************************
 */

function normalizeUnixTimestamp($timestamp)
{
    return sprintf("%01.1f", $timestamp);
}

/**
 * Convert unix timestamp to date.
 *
 * @param string|float|number validUntil
 * @return DateTime
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
function makeHash(string $authUser, string $secretKey, $validUntil = null, array $extra = null): string
{
    if (!$extra) {
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
    if (!$extra) {
        $extra = array();
    }

    if (!$validUntil) {
        $validUntil = makeValidUntil($lifetime);
    } else {
        try {
            unixTimestampToDate($validUntil);
        } catch (Exception $err) {
            return null;
        }
    }

    $hash = makeHash($authUser, $secretKey, $validUntil, $extra);
//    echo("----------------------- hash ----------------------\n");
//    print_r($hash);
//    echo("\n");
    $signature = base64_encode($hash);

    return new Signature($signature, $authUser, $validUntil, $extra);
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


/**
 * Sample payload
 */
const JSON = <<<EOD
{
    "order_lines": [{
        "quantity": 4,
        "product_id": "8273401260171",
        "product_name": "himself",
        "product_description": "Man movement another skill draw great late.",
        "product_price_excl_tax": 7685,
        "product_price_incl_tax": 8684,
        "product_tax_rate_percentage": 13
    }, {
        "quantity": 5,
        "product_id": "6760122207575",
        "product_name": "someone",
        "product_description": "Including couple happen ago hotel son know list.",
        "product_price_excl_tax": 19293,
        "product_price_incl_tax": 20064,
        "product_tax_rate_percentage": 4
    }, {
        "quantity": 1,
        "product_id": "5014352615527",
        "product_name": "able",
        "product_description": "Simply reason bring manager with lot.",
        "product_price_excl_tax": 39538,
        "product_price_incl_tax": 41910,
        "product_tax_rate_percentage": 6
    }, {
        "quantity": 1,
        "product_id": "4666517682328",
        "product_name": "person",
        "product_description": "Arrive government such arm conference program every.",
        "product_price_excl_tax": 18794,
        "product_price_incl_tax": 18794,
        "product_tax_rate_percentage": 0
    }, {
        "quantity": 2,
        "product_id": "3428396033957",
        "product_name": "chance",
        "product_description": "Ever campaign next store far stop and.",
        "product_price_excl_tax": 26894,
        "product_price_incl_tax": 29314,
        "product_tax_rate_percentage": 9
    }, {
        "quantity": 4,
        "product_id": "4822589619741",
        "product_name": "style",
        "product_description": "Song any season pick box chance.",
        "product_price_excl_tax": 17037,
        "product_price_incl_tax": 19422,
        "product_tax_rate_percentage": 14
    }],
    "webshop_id": "4381a041-11cd-43fa-9fb4-c558bac1bd5e",
    "order_id": "lTAGlTOHtKiBdvRvmhSw",
    "amount": 491605,
    "currency": "EUR",
    "company": {
        "name": "Siemens",
        "registration_number": "LhkvLTWNTVNxlMKfBruq",
        "vat_number": "RNQfPcPtnbDFvQRbJeNJ",
        "website": "https://www.nedschroef.com/",
        "country": "NL"
    },
    "user": {
        "first_name": "Noor",
        "last_name": "van Praagh",
        "email": "juliegoyaerts-van-waderle@gmail.com",
        "phone_number": "+31475013353"
    },
    "shipping": {
        "street": "Femkeboulevard",
        "house_number": "7",
        "city": "Noord-Sleen",
        "postal_code": "1784KL",
        "country": "NL"
    },
    "billing": {
        "street": "Pippasteeg",
        "house_number": "35",
        "city": "Ospel",
        "postal_code": "6385 VA",
        "country": "NL"
    }
}
EOD;

define("PAYLOAD", json_decode(JSON, true));

/**
 * Shared secret
 */
const SECRET_KEY = "UxuhnPaO4vKA";

/**
 * Auth user.
 */
const AUTH_USER = "me@example.com";


/**
 * Fields to sign
 */
const SIGNATURE_DATA_KEYS = array(
    "webshop_id",
    "order_id",
    "company",
    "order_lines",
    "amount",
    "currency",
    "user",
    "shipping",
    "billing",
);


/**
 * Prepare a dictionary with data to sign.
 *
 * @param array $requestData
 * @return array
 */
function getSignatureData(array $requestData): array {
    $signatureData = array();
    foreach ($requestData as $key => $value) {
        if (in_array($key, SIGNATURE_DATA_KEYS)) {
            $signatureData[$key] = $value;
        }
    }
    return $signatureData;
}

/**
 * *******************************************
 * *************** Usage examples ************
 * *******************************************
 */

echo("\n === \n PAYLOAD \n === \n");
print_r(PAYLOAD);

/**
 * Signature data
 */
$signatureData = getSignatureData(PAYLOAD);

echo("\n === \n signatureData \n === \n");
print_r($signatureData);

$sortedSignatureData = dictToOrderedDict($signatureData);

echo("\n === \n sortedSignatureData \n === \n");
print_r($sortedSignatureData);

$validUntil0 = makeValidUntil();
echo("\n === \n validUntil0 \n === \n");
print_r($validUntil0);

$validUntil = normalizeUnixTimestamp(1628717009.0);
echo("\n === \n validUntil \n === \n");
print_r($validUntil);

$keys = dictKeys(PAYLOAD);
echo("\n === \n keys \n === \n");
print_r($keys);

$dateFromUnitTimestamp = unixTimestampToDate($validUntil);
echo("\n === \n dateFromUnitTimestamp \n === \n");
print_r($dateFromUnitTimestamp);

$validUntil2 = 1631087737.0;
$dateFromUnitTimestamp2 = unixTimestampToDate($validUntil2);
echo("\n === \n dateFromUnitTimestamp2 \n === \n");
print_r($dateFromUnitTimestamp2);

$validUntil3 = '1631087737.0';
$dateFromUnitTimestamp3 = unixTimestampToDate($validUntil3);
echo("\n === \n dateFromUnitTimestamp3 \n === \n");
print_r($dateFromUnitTimestamp3);

$validUntil4 = 1629418639.1;
$dateFromUnitTimestamp4 = unixTimestampToDate($validUntil4);
echo("\n === \n dateFromUnitTimestamp4 \n === \n");
print_r($dateFromUnitTimestamp4);

$validUntil5 = '1629418639.1';
$dateFromUnitTimestamp5 = unixTimestampToDate($validUntil5);
echo("\n === \n dateFromUnitTimestamp5 \n === \n");
print_r($dateFromUnitTimestamp5);

$base = getBase(
    $authUser=AUTH_USER,
    $validUntil,
    $extra=null,
);
echo("\n === \n base \n === \n");
print_r($base);

$base2 = getBase(
    $authUser=AUTH_USER,
    $validUntil,
    $extra=["1" => "1", "2" => "2"],
);
echo("\n === \n base2 \n === \n");
print_r($base2);

$encodedData = sortedURLEncode($signatureData);
echo("\n === \n encodedData \n === \n");
print_r($encodedData);

$encodedData2 = sortedURLEncode($signatureData, $quoted=false);
echo("\n === \n encodedData2 \n === \n");
print_r($encodedData2);

$orderedPayload = dictToOrderedDict(PAYLOAD);
echo("\n === \n orderedPayload \n === \n");
print_r($orderedPayload);

$hash = makeHash(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $extra=$sortedSignatureData,
);
echo("\n === \n hash \n === \n");
print_r($hash);

$hash2 = makeHash(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $extra=null,
);
echo("\n === \n hash2 \n === \n");
print_r($hash2);

$hash3 = makeHash(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $extra=["1"=>"1", "2"=>"2"],
);
echo("\n === \n hash3 \n === \n");
print_r($hash3);


$signature = generateSignature(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $lifetime=SIGNATURE_LIFETIME,
    $extra=null,
);
echo("\n === \n signature \n === \n");
print_r($signature);

$signature2 = generateSignature(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $lifetime=SIGNATURE_LIFETIME,
    $extra=["1"=>"1", "2"=>"2"]
);
echo("\n === \n signature2 \n === \n");
print_r($signature2);

$signature3 = generateSignature(
    $authUser=AUTH_USER,
    $secretKey=SECRET_KEY,
    $validUntil,
    $lifetime=SIGNATURE_LIFETIME,
    $extra=$signatureData
);
echo("\n === \n signature3 \n === \n");
print_r($signature3);

echo("\n");
