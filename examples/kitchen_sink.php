<?php

/**
 * Sample payload
 */

namespace SKA\Examples;

use function SKA\dictKeys;
use function SKA\dictToOrderedDict;
use function SKA\generateSignature;
use function SKA\getBase;
use function SKA\makeHash;
use function SKA\makeValidUntil;
use function SKA\normalizeUnixTimestamp;
use function SKA\signatureToDict;
use function SKA\sortedURLEncode;
use function SKA\unixTimestampToDate;
use const SKA\DEFAULT_SIGNATURE_PARAM;
use const SKA\SIGNATURE_LIFETIME;

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

$signatureDict = signatureToDict(
    PAYLOAD["webshop_id"],
    SECRET_KEY,
    $validUntil,
    SIGNATURE_LIFETIME,
    $signatureData,
    DEFAULT_SIGNATURE_PARAM,
    "webshop_id",
);
echo("\n === \n signatureDict \n === \n");
print_r($signatureDict);

$signatureDict2 = signatureToDict(
    PAYLOAD["webshop_id"],
    SECRET_KEY,
    $validUntil,
    SIGNATURE_LIFETIME,
    ["1" => "1", "2" => "2"],
    DEFAULT_SIGNATURE_PARAM,
    $authUserParam="webshop_id",
);
echo("\n === \n signatureDict2 \n === \n");
print_r($signatureDict2);

echo("\n");
