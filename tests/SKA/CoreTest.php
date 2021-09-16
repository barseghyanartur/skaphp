<?php

declare(strict_types=1);

namespace SKA\Tests;

use SKA;
use PHPUnit\Framework\TestCase;


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


define("SIGNATURE_DATA", getSignatureData(PAYLOAD));
define("VALID_UNTIL", SKA\formatValidUntil("1628717009.0"));

/**
 * Tests.
 */



final class CoreTest extends TestCase
{

    protected function setUp(): void
    {
//        $this->placeholder = new Placeholder('Artur Barseghyan says: ');
//        self::assertSame('Artur Barseghyan says: Hello', $this->placeholder->echo('Hello'));
    }

    public function testSortedURLEncode(): void
    {
        $encodedData = SKA\sortedURLEncode(SIGNATURE_DATA);
        $expectedData = "amount%3D491605%26billing%3D%7B%22city%22%3A%22Ospel%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%2235%22%2C%22postal_code%22%3A%226385%20VA%22%2C%22street%22%3A%22Pippasteeg%22%7D%26company%3D%7B%22country%22%3A%22NL%22%2C%22name%22%3A%22Siemens%22%2C%22registration_number%22%3A%22LhkvLTWNTVNxlMKfBruq%22%2C%22vat_number%22%3A%22RNQfPcPtnbDFvQRbJeNJ%22%2C%22website%22%3A%22https%3A%2F%2Fwww.nedschroef.com%2F%22%7D%26currency%3DEUR%26order_id%3DlTAGlTOHtKiBdvRvmhSw%26order_lines%3D%5B%7B%22product_description%22%3A%22Man%20movement%20another%20skill%20draw%20great%20late.%22%2C%22product_id%22%3A%228273401260171%22%2C%22product_name%22%3A%22himself%22%2C%22product_price_excl_tax%22%3A7685%2C%22product_price_incl_tax%22%3A8684%2C%22product_tax_rate_percentage%22%3A13%2C%22quantity%22%3A4%7D%2C%7B%22product_description%22%3A%22Including%20couple%20happen%20ago%20hotel%20son%20know%20list.%22%2C%22product_id%22%3A%226760122207575%22%2C%22product_name%22%3A%22someone%22%2C%22product_price_excl_tax%22%3A19293%2C%22product_price_incl_tax%22%3A20064%2C%22product_tax_rate_percentage%22%3A4%2C%22quantity%22%3A5%7D%2C%7B%22product_description%22%3A%22Simply%20reason%20bring%20manager%20with%20lot.%22%2C%22product_id%22%3A%225014352615527%22%2C%22product_name%22%3A%22able%22%2C%22product_price_excl_tax%22%3A39538%2C%22product_price_incl_tax%22%3A41910%2C%22product_tax_rate_percentage%22%3A6%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Arrive%20government%20such%20arm%20conference%20program%20every.%22%2C%22product_id%22%3A%224666517682328%22%2C%22product_name%22%3A%22person%22%2C%22product_price_excl_tax%22%3A18794%2C%22product_price_incl_tax%22%3A18794%2C%22product_tax_rate_percentage%22%3A0%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Ever%20campaign%20next%20store%20far%20stop%20and.%22%2C%22product_id%22%3A%223428396033957%22%2C%22product_name%22%3A%22chance%22%2C%22product_price_excl_tax%22%3A26894%2C%22product_price_incl_tax%22%3A29314%2C%22product_tax_rate_percentage%22%3A9%2C%22quantity%22%3A2%7D%2C%7B%22product_description%22%3A%22Song%20any%20season%20pick%20box%20chance.%22%2C%22product_id%22%3A%224822589619741%22%2C%22product_name%22%3A%22style%22%2C%22product_price_excl_tax%22%3A17037%2C%22product_price_incl_tax%22%3A19422%2C%22product_tax_rate_percentage%22%3A14%2C%22quantity%22%3A4%7D%5D%26shipping%3D%7B%22city%22%3A%22Noord-Sleen%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%227%22%2C%22postal_code%22%3A%221784KL%22%2C%22street%22%3A%22Femkeboulevard%22%7D%26user%3D%7B%22email%22%3A%22juliegoyaerts-van-waderle%40gmail.com%22%2C%22first_name%22%3A%22Noor%22%2C%22last_name%22%3A%22van%20Praagh%22%2C%22phone_number%22%3A%22%2B31475013353%22%7D%26webshop_id%3D4381a041-11cd-43fa-9fb4-c558bac1bd5e";
        self::assertSame($encodedData, $expectedData);

        $encodedDataUnquoted = SKA\sortedURLEncode(SIGNATURE_DATA, false);
        $expectedDataUnquoted = 'amount=491605&billing={"city":"Ospel","country":"NL","house_number":"35","postal_code":"6385 VA","street":"Pippasteeg"}&company={"country":"NL","name":"Siemens","registration_number":"LhkvLTWNTVNxlMKfBruq","vat_number":"RNQfPcPtnbDFvQRbJeNJ","website":"https://www.nedschroef.com/"}&currency=EUR&order_id=lTAGlTOHtKiBdvRvmhSw&order_lines=[{"product_description":"Man movement another skill draw great late.","product_id":"8273401260171","product_name":"himself","product_price_excl_tax":7685,"product_price_incl_tax":8684,"product_tax_rate_percentage":13,"quantity":4},{"product_description":"Including couple happen ago hotel son know list.","product_id":"6760122207575","product_name":"someone","product_price_excl_tax":19293,"product_price_incl_tax":20064,"product_tax_rate_percentage":4,"quantity":5},{"product_description":"Simply reason bring manager with lot.","product_id":"5014352615527","product_name":"able","product_price_excl_tax":39538,"product_price_incl_tax":41910,"product_tax_rate_percentage":6,"quantity":1},{"product_description":"Arrive government such arm conference program every.","product_id":"4666517682328","product_name":"person","product_price_excl_tax":18794,"product_price_incl_tax":18794,"product_tax_rate_percentage":0,"quantity":1},{"product_description":"Ever campaign next store far stop and.","product_id":"3428396033957","product_name":"chance","product_price_excl_tax":26894,"product_price_incl_tax":29314,"product_tax_rate_percentage":9,"quantity":2},{"product_description":"Song any season pick box chance.","product_id":"4822589619741","product_name":"style","product_price_excl_tax":17037,"product_price_incl_tax":19422,"product_tax_rate_percentage":14,"quantity":4}]&shipping={"city":"Noord-Sleen","country":"NL","house_number":"7","postal_code":"1784KL","street":"Femkeboulevard"}&user={"email":"juliegoyaerts-van-waderle@gmail.com","first_name":"Noor","last_name":"van Praagh","phone_number":"+31475013353"}&webshop_id=4381a041-11cd-43fa-9fb4-c558bac1bd5e';
        self::assertSame($encodedDataUnquoted, $expectedDataUnquoted);
    }

    public function testSortedSignatureData(): void
    {
        $sortedSignatureData = SKA\dictToOrderedDict(SIGNATURE_DATA);
        $expectedSortedSignatureDataJSON = <<<EOD
            {
                "amount": 491605,
                "billing": {
                    "city": "Ospel",
                    "country": "NL",
                    "house_number": "35",
                    "postal_code": "6385 VA",
                    "street": "Pippasteeg"
                },
                "company": {
                    "country": "NL",
                    "name": "Siemens",
                    "registration_number": "LhkvLTWNTVNxlMKfBruq",
                    "vat_number": "RNQfPcPtnbDFvQRbJeNJ",
                    "website": "https://www.nedschroef.com/"
                },
                "currency": "EUR",
                "order_id": "lTAGlTOHtKiBdvRvmhSw",
                "order_lines": [
                    {
                        "product_description": "Man movement another skill draw great late.",
                        "product_id": "8273401260171",
                        "product_name": "himself",
                        "product_price_excl_tax": 7685,
                        "product_price_incl_tax": 8684,
                        "product_tax_rate_percentage": 13,
                        "quantity": 4
                    },
                    {
                        "product_description": "Including couple happen ago hotel son know list.",
                        "product_id": "6760122207575",
                        "product_name": "someone",
                        "product_price_excl_tax": 19293,
                        "product_price_incl_tax": 20064,
                        "product_tax_rate_percentage": 4,
                        "quantity": 5
                    },
                    {
                        "product_description": "Simply reason bring manager with lot.",
                        "product_id": "5014352615527",
                        "product_name": "able",
                        "product_price_excl_tax": 39538,
                        "product_price_incl_tax": 41910,
                        "product_tax_rate_percentage": 6,
                        "quantity": 1
                    },
                    {
                        "product_description": "Arrive government such arm conference program every.",
                        "product_id": "4666517682328",
                        "product_name": "person",
                        "product_price_excl_tax": 18794,
                        "product_price_incl_tax": 18794,
                        "product_tax_rate_percentage": 0,
                        "quantity": 1
                    },
                    {
                        "product_description": "Ever campaign next store far stop and.",
                        "product_id": "3428396033957",
                        "product_name": "chance",
                        "product_price_excl_tax": 26894,
                        "product_price_incl_tax": 29314,
                        "product_tax_rate_percentage": 9,
                        "quantity": 2
                    },
                    {
                        "product_description": "Song any season pick box chance.",
                        "product_id": "4822589619741",
                        "product_name": "style",
                        "product_price_excl_tax": 17037,
                        "product_price_incl_tax": 19422,
                        "product_tax_rate_percentage": 14,
                        "quantity": 4
                    }
                ],
                "shipping": {
                    "city": "Noord-Sleen",
                    "country": "NL",
                    "house_number": "7",
                    "postal_code": "1784KL",
                    "street": "Femkeboulevard"
                },
                "user": {
                    "email": "juliegoyaerts-van-waderle@gmail.com",
                    "first_name": "Noor",
                    "last_name": "van Praagh",
                    "phone_number": "+31475013353"
                },
                "webshop_id": "4381a041-11cd-43fa-9fb4-c558bac1bd5e"
            }
            EOD;
        $expectedSortedSignatureData = json_decode($expectedSortedSignatureDataJSON, true);
        self::assertEquals($sortedSignatureData, $expectedSortedSignatureData);
    }

    public function testDictKeys(): void
    {
        $keys = SKA\dictKeys(PAYLOAD);
        $expectedKeys = [
            "amount",
            "billing",
            "company",
            "currency",
            "order_id",
            "order_lines",
            "shipping",
            "user",
            "webshop_id",
        ];
        self::assertEquals($keys, $expectedKeys);
    }

    public function testGenerateSignature(): void
    {
        // Signature test case 1
        $signature = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            null
        );
        $expectedSignature = new SKA\Signature(
            "WTjN2wPENDW1gCHEVPKz3IXlE0g=",
            "me@example.com",
            "1628717009.0",
            []
        );
        self::assertEquals($signature, $expectedSignature);

        // Signature test case 2
        $signature2 = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            [1=>"1", 2=>"2"]
        );
        $expectedSignature2 = new SKA\Signature(
            "ZGncnzq0NlcMe2qMDqR02yfonR0=",
            "me@example.com",
            "1628717009.0",
            [1=>"1", 2=>"2"]
        );
        self::assertEquals($signature2, $expectedSignature2);

        // Signature test case 3
        $signature3 = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            SIGNATURE_DATA
        );
        $extra3JSON = <<<EOD
            {
                "order_lines": [
                    {
                        "quantity": 4,
                        "product_id": "8273401260171",
                        "product_name": "himself",
                        "product_description": "Man movement another skill draw great late.",
                        "product_price_excl_tax": 7685,
                        "product_price_incl_tax": 8684,
                        "product_tax_rate_percentage": 13
                    },
                    {
                        "quantity": 5,
                        "product_id": "6760122207575",
                        "product_name": "someone",
                        "product_description": "Including couple happen ago hotel son know list.",
                        "product_price_excl_tax": 19293,
                        "product_price_incl_tax": 20064,
                        "product_tax_rate_percentage": 4
                    },
                    {
                        "quantity": 1,
                        "product_id": "5014352615527",
                        "product_name": "able",
                        "product_description": "Simply reason bring manager with lot.",
                        "product_price_excl_tax": 39538,
                        "product_price_incl_tax": 41910,
                        "product_tax_rate_percentage": 6
                    },
                    {
                        "quantity": 1,
                        "product_id": "4666517682328",
                        "product_name": "person",
                        "product_description": "Arrive government such arm conference program every.",
                        "product_price_excl_tax": 18794,
                        "product_price_incl_tax": 18794,
                        "product_tax_rate_percentage": 0
                    },
                    {
                        "quantity": 2,
                        "product_id": "3428396033957",
                        "product_name": "chance",
                        "product_description": "Ever campaign next store far stop and.",
                        "product_price_excl_tax": 26894,
                        "product_price_incl_tax": 29314,
                        "product_tax_rate_percentage": 9
                    },
                    {
                        "quantity": 4,
                        "product_id": "4822589619741",
                        "product_name": "style",
                        "product_description": "Song any season pick box chance.",
                        "product_price_excl_tax": 17037,
                        "product_price_incl_tax": 19422,
                        "product_tax_rate_percentage": 14
                    }
                ],
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
        $extra3 = json_decode($extra3JSON, true);
        $expectedSignature3 = new SKA\Signature(
            "pHVmnlbzb0hIJ+EWcRhRA3Ajrx8=",
            "me@example.com",
            "1628717009.0",
            $extra3
        );
        self::assertEquals($signature3, $expectedSignature3);
    }

    public function testSignatureToDict(): void
    {
        // Test case 1
        $signatureDict = SKA\signatureToDict(
            PAYLOAD["webshop_id"],
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            SIGNATURE_DATA,
            SKA\DEFAULT_SIGNATURE_PARAM,
            "webshop_id"
        );
        $expectedSignatureDictJSON = <<<EOD
            {
                "signature": "+r9u8ztA7oEe9mTGMxKDVJ/8Sec=",
                "valid_until": "1628717009.0",
                "extra": "amount,billing,company,currency,order_id,order_lines,shipping,user,webshop_id",
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
        $expectedSignatureDict = json_decode($expectedSignatureDictJSON, true);
        self::assertEquals($signatureDict, $expectedSignatureDict);

        // Test case 2
        $signatureDict2 = SKA\signatureToDict(
            PAYLOAD["webshop_id"],
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            ["one" => "1", "two" => "2"],
            SKA\DEFAULT_SIGNATURE_PARAM,
            "webshop_id"
        );
        $expectedSignatureDict2 = [
            "one" => "1",
            "two" => "2",
            "signature" => "Fg4s3QErL2GySta8VhNBXaaBSDM=",
            "webshop_id" => "4381a041-11cd-43fa-9fb4-c558bac1bd5e",
            "valid_until" => "1628717009.0",
            "extra" => "one,two",
        ];
        self::assertEquals($signatureDict2, $expectedSignatureDict2);
    }

    public function testGetBase(): void
    {
        // Test case 1
        $base = SKA\getBase(AUTH_USER, VALID_UNTIL, null);
        $expectedBase = "1628717009.0_me@example.com";
        self::assertEquals($base, $expectedBase);

        // Test case 2
        $base2 = SKA\getBase(AUTH_USER, VALID_UNTIL, ["one" => "1", "two" => "2"]);
        $expectedBase2 = "1628717009.0_me@example.com_one%3D1%26two%3D2";
        self::assertEquals($base2, $expectedBase2);
    }
}
