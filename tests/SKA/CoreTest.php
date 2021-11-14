<?php
/**
 * SKA - sign data, using symmetric-key algorithm encryption.
 *
 * Lets you easily sign data, using symmetric-key algorithm encryption.
 * Allows you to validate signed data and identify possible validation
 * errors. Uses sha/hmac for signature encryption. Comes with shortcut
 * functions for signing (and validating) dictionaries.
 *
 * PHP version > 7.2
 *
 * LICENSE: This source file is subject to MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Encryption
 * @package    SKA
 * @author     Artur Barseghyan <artur.barseghyan@gmail.com>
 * @copyright  2021 Artur Barseghyan
 * @license    https://opensource.org/licenses/MIT MIT license
 * @version    SVN: $Id$
 * @link       https://github.com/barseghyanartur/skaphp
 */

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
    "billing"
);


/**
 * Prepare a dictionary with data to sign.
 *
 * @param array $requestData
 * @return array
 */
function getSignatureData(array $requestData): array
{
    $signatureData = array();
    foreach ($requestData as $key => $value) {
        if (in_array($key, SIGNATURE_DATA_KEYS, true)) {
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
    }

    public function testSortedURLEncode(): void
    {
        // Test case 1 - Encoded data quoted
        $encodedData = SKA\sortedURLEncode(SIGNATURE_DATA);
        $expectedData = "amount%3D491605%26billing%3D%7B%22city%22%3A%22Ospel%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%2235%22%2C%22postal_code%22%3A%226385%20VA%22%2C%22street%22%3A%22Pippasteeg%22%7D%26company%3D%7B%22country%22%3A%22NL%22%2C%22name%22%3A%22Siemens%22%2C%22registration_number%22%3A%22LhkvLTWNTVNxlMKfBruq%22%2C%22vat_number%22%3A%22RNQfPcPtnbDFvQRbJeNJ%22%2C%22website%22%3A%22https%3A%2F%2Fwww.nedschroef.com%2F%22%7D%26currency%3DEUR%26order_id%3DlTAGlTOHtKiBdvRvmhSw%26order_lines%3D%5B%7B%22product_description%22%3A%22Man%20movement%20another%20skill%20draw%20great%20late.%22%2C%22product_id%22%3A%228273401260171%22%2C%22product_name%22%3A%22himself%22%2C%22product_price_excl_tax%22%3A7685%2C%22product_price_incl_tax%22%3A8684%2C%22product_tax_rate_percentage%22%3A13%2C%22quantity%22%3A4%7D%2C%7B%22product_description%22%3A%22Including%20couple%20happen%20ago%20hotel%20son%20know%20list.%22%2C%22product_id%22%3A%226760122207575%22%2C%22product_name%22%3A%22someone%22%2C%22product_price_excl_tax%22%3A19293%2C%22product_price_incl_tax%22%3A20064%2C%22product_tax_rate_percentage%22%3A4%2C%22quantity%22%3A5%7D%2C%7B%22product_description%22%3A%22Simply%20reason%20bring%20manager%20with%20lot.%22%2C%22product_id%22%3A%225014352615527%22%2C%22product_name%22%3A%22able%22%2C%22product_price_excl_tax%22%3A39538%2C%22product_price_incl_tax%22%3A41910%2C%22product_tax_rate_percentage%22%3A6%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Arrive%20government%20such%20arm%20conference%20program%20every.%22%2C%22product_id%22%3A%224666517682328%22%2C%22product_name%22%3A%22person%22%2C%22product_price_excl_tax%22%3A18794%2C%22product_price_incl_tax%22%3A18794%2C%22product_tax_rate_percentage%22%3A0%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Ever%20campaign%20next%20store%20far%20stop%20and.%22%2C%22product_id%22%3A%223428396033957%22%2C%22product_name%22%3A%22chance%22%2C%22product_price_excl_tax%22%3A26894%2C%22product_price_incl_tax%22%3A29314%2C%22product_tax_rate_percentage%22%3A9%2C%22quantity%22%3A2%7D%2C%7B%22product_description%22%3A%22Song%20any%20season%20pick%20box%20chance.%22%2C%22product_id%22%3A%224822589619741%22%2C%22product_name%22%3A%22style%22%2C%22product_price_excl_tax%22%3A17037%2C%22product_price_incl_tax%22%3A19422%2C%22product_tax_rate_percentage%22%3A14%2C%22quantity%22%3A4%7D%5D%26shipping%3D%7B%22city%22%3A%22Noord-Sleen%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%227%22%2C%22postal_code%22%3A%221784KL%22%2C%22street%22%3A%22Femkeboulevard%22%7D%26user%3D%7B%22email%22%3A%22juliegoyaerts-van-waderle%40gmail.com%22%2C%22first_name%22%3A%22Noor%22%2C%22last_name%22%3A%22van%20Praagh%22%2C%22phone_number%22%3A%22%2B31475013353%22%7D%26webshop_id%3D4381a041-11cd-43fa-9fb4-c558bac1bd5e";
        self::assertSame($encodedData, $expectedData);

        // Test case 2 - Encoded data unquoted
        $encodedDataUnquoted = SKA\sortedURLEncode(SIGNATURE_DATA, false);
        $expectedDataUnquoted = 'amount=491605&billing={"city":"Ospel","country":"NL","house_number":"35","postal_code":"6385 VA","street":"Pippasteeg"}&company={"country":"NL","name":"Siemens","registration_number":"LhkvLTWNTVNxlMKfBruq","vat_number":"RNQfPcPtnbDFvQRbJeNJ","website":"https://www.nedschroef.com/"}&currency=EUR&order_id=lTAGlTOHtKiBdvRvmhSw&order_lines=[{"product_description":"Man movement another skill draw great late.","product_id":"8273401260171","product_name":"himself","product_price_excl_tax":7685,"product_price_incl_tax":8684,"product_tax_rate_percentage":13,"quantity":4},{"product_description":"Including couple happen ago hotel son know list.","product_id":"6760122207575","product_name":"someone","product_price_excl_tax":19293,"product_price_incl_tax":20064,"product_tax_rate_percentage":4,"quantity":5},{"product_description":"Simply reason bring manager with lot.","product_id":"5014352615527","product_name":"able","product_price_excl_tax":39538,"product_price_incl_tax":41910,"product_tax_rate_percentage":6,"quantity":1},{"product_description":"Arrive government such arm conference program every.","product_id":"4666517682328","product_name":"person","product_price_excl_tax":18794,"product_price_incl_tax":18794,"product_tax_rate_percentage":0,"quantity":1},{"product_description":"Ever campaign next store far stop and.","product_id":"3428396033957","product_name":"chance","product_price_excl_tax":26894,"product_price_incl_tax":29314,"product_tax_rate_percentage":9,"quantity":2},{"product_description":"Song any season pick box chance.","product_id":"4822589619741","product_name":"style","product_price_excl_tax":17037,"product_price_incl_tax":19422,"product_tax_rate_percentage":14,"quantity":4}]&shipping={"city":"Noord-Sleen","country":"NL","house_number":"7","postal_code":"1784KL","street":"Femkeboulevard"}&user={"email":"juliegoyaerts-van-waderle@gmail.com","first_name":"Noor","last_name":"van Praagh","phone_number":"+31475013353"}&webshop_id=4381a041-11cd-43fa-9fb4-c558bac1bd5e';
        self::assertSame($encodedDataUnquoted, $expectedDataUnquoted);

        // Test case 3 - $valueDumper is null
        $encodedData = SKA\sortedURLEncode(SIGNATURE_DATA, true, null);
        $expectedData = "amount%3D491605%26billing%3D%7B%22city%22%3A%22Ospel%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%2235%22%2C%22postal_code%22%3A%226385%20VA%22%2C%22street%22%3A%22Pippasteeg%22%7D%26company%3D%7B%22country%22%3A%22NL%22%2C%22name%22%3A%22Siemens%22%2C%22registration_number%22%3A%22LhkvLTWNTVNxlMKfBruq%22%2C%22vat_number%22%3A%22RNQfPcPtnbDFvQRbJeNJ%22%2C%22website%22%3A%22https%3A%2F%2Fwww.nedschroef.com%2F%22%7D%26currency%3DEUR%26order_id%3DlTAGlTOHtKiBdvRvmhSw%26order_lines%3D%5B%7B%22product_description%22%3A%22Man%20movement%20another%20skill%20draw%20great%20late.%22%2C%22product_id%22%3A%228273401260171%22%2C%22product_name%22%3A%22himself%22%2C%22product_price_excl_tax%22%3A7685%2C%22product_price_incl_tax%22%3A8684%2C%22product_tax_rate_percentage%22%3A13%2C%22quantity%22%3A4%7D%2C%7B%22product_description%22%3A%22Including%20couple%20happen%20ago%20hotel%20son%20know%20list.%22%2C%22product_id%22%3A%226760122207575%22%2C%22product_name%22%3A%22someone%22%2C%22product_price_excl_tax%22%3A19293%2C%22product_price_incl_tax%22%3A20064%2C%22product_tax_rate_percentage%22%3A4%2C%22quantity%22%3A5%7D%2C%7B%22product_description%22%3A%22Simply%20reason%20bring%20manager%20with%20lot.%22%2C%22product_id%22%3A%225014352615527%22%2C%22product_name%22%3A%22able%22%2C%22product_price_excl_tax%22%3A39538%2C%22product_price_incl_tax%22%3A41910%2C%22product_tax_rate_percentage%22%3A6%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Arrive%20government%20such%20arm%20conference%20program%20every.%22%2C%22product_id%22%3A%224666517682328%22%2C%22product_name%22%3A%22person%22%2C%22product_price_excl_tax%22%3A18794%2C%22product_price_incl_tax%22%3A18794%2C%22product_tax_rate_percentage%22%3A0%2C%22quantity%22%3A1%7D%2C%7B%22product_description%22%3A%22Ever%20campaign%20next%20store%20far%20stop%20and.%22%2C%22product_id%22%3A%223428396033957%22%2C%22product_name%22%3A%22chance%22%2C%22product_price_excl_tax%22%3A26894%2C%22product_price_incl_tax%22%3A29314%2C%22product_tax_rate_percentage%22%3A9%2C%22quantity%22%3A2%7D%2C%7B%22product_description%22%3A%22Song%20any%20season%20pick%20box%20chance.%22%2C%22product_id%22%3A%224822589619741%22%2C%22product_name%22%3A%22style%22%2C%22product_price_excl_tax%22%3A17037%2C%22product_price_incl_tax%22%3A19422%2C%22product_tax_rate_percentage%22%3A14%2C%22quantity%22%3A4%7D%5D%26shipping%3D%7B%22city%22%3A%22Noord-Sleen%22%2C%22country%22%3A%22NL%22%2C%22house_number%22%3A%227%22%2C%22postal_code%22%3A%221784KL%22%2C%22street%22%3A%22Femkeboulevard%22%7D%26user%3D%7B%22email%22%3A%22juliegoyaerts-van-waderle%40gmail.com%22%2C%22first_name%22%3A%22Noor%22%2C%22last_name%22%3A%22van%20Praagh%22%2C%22phone_number%22%3A%22%2B31475013353%22%7D%26webshop_id%3D4381a041-11cd-43fa-9fb4-c558bac1bd5e";
        self::assertSame($encodedData, $expectedData);

        // Test case 4 - $valueDumper is null
        $encodedDataUnquoted = SKA\sortedURLEncode(SIGNATURE_DATA, false, null);
        $expectedDataUnquoted = 'amount=491605&billing={"city":"Ospel","country":"NL","house_number":"35","postal_code":"6385 VA","street":"Pippasteeg"}&company={"country":"NL","name":"Siemens","registration_number":"LhkvLTWNTVNxlMKfBruq","vat_number":"RNQfPcPtnbDFvQRbJeNJ","website":"https://www.nedschroef.com/"}&currency=EUR&order_id=lTAGlTOHtKiBdvRvmhSw&order_lines=[{"product_description":"Man movement another skill draw great late.","product_id":"8273401260171","product_name":"himself","product_price_excl_tax":7685,"product_price_incl_tax":8684,"product_tax_rate_percentage":13,"quantity":4},{"product_description":"Including couple happen ago hotel son know list.","product_id":"6760122207575","product_name":"someone","product_price_excl_tax":19293,"product_price_incl_tax":20064,"product_tax_rate_percentage":4,"quantity":5},{"product_description":"Simply reason bring manager with lot.","product_id":"5014352615527","product_name":"able","product_price_excl_tax":39538,"product_price_incl_tax":41910,"product_tax_rate_percentage":6,"quantity":1},{"product_description":"Arrive government such arm conference program every.","product_id":"4666517682328","product_name":"person","product_price_excl_tax":18794,"product_price_incl_tax":18794,"product_tax_rate_percentage":0,"quantity":1},{"product_description":"Ever campaign next store far stop and.","product_id":"3428396033957","product_name":"chance","product_price_excl_tax":26894,"product_price_incl_tax":29314,"product_tax_rate_percentage":9,"quantity":2},{"product_description":"Song any season pick box chance.","product_id":"4822589619741","product_name":"style","product_price_excl_tax":17037,"product_price_incl_tax":19422,"product_tax_rate_percentage":14,"quantity":4}]&shipping={"city":"Noord-Sleen","country":"NL","house_number":"7","postal_code":"1784KL","street":"Femkeboulevard"}&user={"email":"juliegoyaerts-van-waderle@gmail.com","first_name":"Noor","last_name":"van Praagh","phone_number":"+31475013353"}&webshop_id=4381a041-11cd-43fa-9fb4-c558bac1bd5e';
        self::assertSame($encodedDataUnquoted, $expectedDataUnquoted);

        // Test case 5 - Encoded simple unicode data quoted
        $encodedData = SKA\sortedURLEncode(["one" => "â"]);
        $expectedData = "one%3D%C3%A2";
        self::assertSame($encodedData, $expectedData);

        // Test case 6 - Encoded simple unicode data unquoted
        $encodedData = SKA\sortedURLEncode(["one" => "â"], false);
        $expectedData = "one=â";
        self::assertSame($encodedData, $expectedData);

        // Test case 7 - Encoded complex unicode data quoted
        $encodedData = SKA\sortedURLEncode(["one" => ["value" => "â"]]);
        $expectedData = "one%3D%7B%22value%22%3A%22%5Cu00e2%22%7D";
        self::assertSame($encodedData, $expectedData);

        // Test case 8 - Encoded complex unicode data unquoted
        $encodedData = SKA\sortedURLEncode(["one" => ["value" => "â"]], false);
        $expectedData = 'one={"value":"\\u00e2"}';
        self::assertSame($encodedData, $expectedData);
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
            "webshop_id"
        ];
        self::assertEquals($keys, $expectedKeys);
    }

    public function testMakeHash(): void
    {
        // Case 1
        $hash = base64_encode(SKA\makeHash(AUTH_USER, SECRET_KEY, VALID_UNTIL, ["one" => "â"]));
        $expectedHash = "dlT2WO/jYq7+xcvDEUkCnNW5TxA=";
        self::assertEquals($hash, $expectedHash);

        // Case 2
        $hash2 = base64_encode(SKA\makeHash(AUTH_USER, SECRET_KEY, VALID_UNTIL, ["one" => ["value" => "â"]]));
        $expectedHash2 = "+pA63D4EMF2pcfIlE/dYXyNkhx4=";
        self::assertEquals($hash2, $expectedHash2);
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
            ["one" => "1", "two" => "2"]
        );
        $expectedSignature2 = new SKA\Signature(
            "dFqd/VbWOaY3ROlL89K6JZZsfhE=",
            "me@example.com",
            "1628717009.0",
            ["one" => "1", "two" => "2"]
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
            SIGNATURE_DATA,
            [
                "validUntil" => VALID_UNTIL,
//                "lifetime" => SKA\SIGNATURE_LIFETIME,
//                "signatureParam" => SKA\DEFAULT_SIGNATURE_PARAM,
                "authUserParam" => "webshop_id"
            ]
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
            ["one" => "1", "two" => "2"],
            [
                "validUntil" => VALID_UNTIL,
//                "lifetime" => SKA\SIGNATURE_LIFETIME,
//                "signatureParam" => SKA\DEFAULT_SIGNATURE_PARAM,
                "authUserParam" => "webshop_id"
            ]
        );
        $expectedSignatureDict2 = [
            "one" => "1",
            "two" => "2",
            "signature" => "Fg4s3QErL2GySta8VhNBXaaBSDM=",
            "webshop_id" => "4381a041-11cd-43fa-9fb4-c558bac1bd5e",
            "valid_until" => "1628717009.0",
            "extra" => "one,two"
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

        // Test case 3
        $base3 = SKA\getBase(AUTH_USER, VALID_UNTIL, ["one" => "â"]);
        $expectedBase3 = "1628717009.0_me@example.com_one%3D%C3%A2";
        self::assertEquals($base3, $expectedBase3);

        // Test case 4
        $base4 = SKA\getBase(AUTH_USER, VALID_UNTIL, ["one" => ["value" => "â"]]);
        $expectedBase4 = "1628717009.0_me@example.com_one%3D%7B%22value%22%3A%22%5Cu00e2%22%7D";
        self::assertEquals($base4, $expectedBase4);
    }

    public function testDictToOrderedDict(): void
    {
        $orderedPayload = SKA\dictToOrderedDict(PAYLOAD);
        $expectedOrderedPayloadJSON = <<<EOT
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
                "order_lines": [{
                    "product_description": "Man movement another skill draw great late.",
                    "product_id": "8273401260171",
                    "product_name": "himself",
                    "product_price_excl_tax": 7685,
                    "product_price_incl_tax": 8684,
                    "product_tax_rate_percentage": 13,
                    "quantity": 4
                }, {
                    "product_description": "Including couple happen ago hotel son know list.",
                    "product_id": "6760122207575",
                    "product_name": "someone",
                    "product_price_excl_tax": 19293,
                    "product_price_incl_tax": 20064,
                    "product_tax_rate_percentage": 4,
                    "quantity": 5
                }, {
                    "product_description": "Simply reason bring manager with lot.",
                    "product_id": "5014352615527",
                    "product_name": "able",
                    "product_price_excl_tax": 39538,
                    "product_price_incl_tax": 41910,
                    "product_tax_rate_percentage": 6,
                    "quantity": 1
                }, {
                    "product_description": "Arrive government such arm conference program every.",
                    "product_id": "4666517682328",
                    "product_name": "person",
                    "product_price_excl_tax": 18794,
                    "product_price_incl_tax": 18794,
                    "product_tax_rate_percentage": 0,
                    "quantity": 1
                }, {
                    "product_description": "Ever campaign next store far stop and.",
                    "product_id": "3428396033957",
                    "product_name": "chance",
                    "product_price_excl_tax": 26894,
                    "product_price_incl_tax": 29314,
                    "product_tax_rate_percentage": 9,
                    "quantity": 2
                }, {
                    "product_description": "Song any season pick box chance.",
                    "product_id": "4822589619741",
                    "product_name": "style",
                    "product_price_excl_tax": 17037,
                    "product_price_incl_tax": 19422,
                    "product_tax_rate_percentage": 14,
                    "quantity": 4
                }],
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
EOT;
        $expectedOrderedPayload = json_decode($expectedOrderedPayloadJSON, true);
        self::assertEquals($orderedPayload, $expectedOrderedPayload);
    }

    public function testExtractSignedData(): void
    {
        // Test case 1
        $signatureDict = SKA\signatureToDict(
            PAYLOAD["webshop_id"],
            SECRET_KEY,
            SIGNATURE_DATA,
            [
                "validUntil" => VALID_UNTIL,
//                "lifetime" => SKA\SIGNATURE_LIFETIME,
//                "signatureParam" => SKA\DEFAULT_SIGNATURE_PARAM,
                "authUserParam" => "webshop_id"
            ]
        );
        $extractedSignedData = SKA\extractSignedData(
            $signatureDict,
            SIGNATURE_DATA_KEYS
        );
        $expectedExtractedSignedDataJSON = <<<EOT
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
EOT;
        $expectedExtractedSignedData = json_decode($expectedExtractedSignedDataJSON, true);
        self::assertEquals($extractedSignedData, $expectedExtractedSignedData);
    }

    public function testValidateSignature(): void
    {
        // Test case 1 - valid non-expired signature
        $signature = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            SKA\makeValidUntil(),
            SKA\SIGNATURE_LIFETIME,
            null
        );
        self::assertNotNull($signature);
        $isValidSignature = SKA\validateSignature(
            $signature->signature,
            $signature->authUser,
            SECRET_KEY,
            $signature->validUntil,
            $signature->extra
        );
        self::assertTrue($isValidSignature);

        // Test case 2 - expired signature
        $signature2 = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            VALID_UNTIL,
            SKA\SIGNATURE_LIFETIME,
            null
        );
        self::assertNotNull($signature2);
        $isValidSignature2 = SKA\validateSignature(
            $signature2->signature,
            $signature2->authUser,
            SECRET_KEY,
            $signature2->validUntil,
            $signature2->extra
        );
        self::assertFalse($isValidSignature2);
        self::assertTrue($signature2->isExpired());

        // Test case 3 - valid non-expired signature as object
        $isValidSignature3 = SKA\validateSignature(
            $signature->signature,
            $signature->authUser,
            SECRET_KEY,
            $signature->validUntil,
            $signature->extra,
            true
        );
        self::assertTrue($isValidSignature3->result);
        self::assertEmpty($isValidSignature3->errors);
        self::assertEquals("1", sprintf("%s", $isValidSignature3));

        // Test case 4 - expired signature as object
        $isValidSignature4 = SKA\validateSignature(
            $signature2->signature,
            $signature2->authUser,
            SECRET_KEY,
            $signature2->validUntil,
            $signature2->extra,
            true
        );
        self::assertFalse($isValidSignature4->result);
        self::assertNotEmpty($isValidSignature4->errors);
        self::assertContains(SIGNATURE_TIMESTAMP_EXPIRED, $isValidSignature4->errors);
        self::assertEquals(SIGNATURE_TIMESTAMP_EXPIRED, $isValidSignature4->message());
        self::assertNotContains(INVALID_SIGNATURE, $isValidSignature4->errors);

        // Test case 5 - invalid signature as object
        $isValidSignature4 = SKA\validateSignature(
            'invalid-signature',
            $signature2->authUser,
            SECRET_KEY,
            $signature2->validUntil,
            $signature2->extra,
            true
        );
        self::assertFalse($isValidSignature4->result);
        self::assertNotEmpty($isValidSignature4->errors);
        self::assertContains(SIGNATURE_TIMESTAMP_EXPIRED, $isValidSignature4->errors);
        self::assertContains(INVALID_SIGNATURE, $isValidSignature4->errors);

        // Test case 6 - valid non-expired signature when $validUntil provided is null
        $signature = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            null,
            SKA\SIGNATURE_LIFETIME,
            null
        );
        self::assertNotNull($signature);
        $isValidSignature = SKA\validateSignature(
            $signature->signature,
            $signature->authUser,
            SECRET_KEY,
            $signature->validUntil,
            $signature->extra
        );
        self::assertTrue($isValidSignature);
    }

    public function testValidateSignedRequestData(): void
    {
        // Test case 1
        $signatureDict = SKA\signatureToDict(
            PAYLOAD["webshop_id"],
            SECRET_KEY,
            SIGNATURE_DATA,
            [
                "validUntil" => SKA\makeValidUntil(),
                "authUserParam" => "webshop_id"
            ]
        );
        $validationResult = SKA\validateSignedRequestData(
            $signatureDict,
            SECRET_KEY,
            [
                "authUserParam" => "webshop_id"
            ]
        );
        self::assertTrue($validationResult);

        // Test case 2 - expired signature
        $signatureDict2 = SKA\signatureToDict(
            PAYLOAD["webshop_id"],
            SECRET_KEY,
            SIGNATURE_DATA,
            [
                "validUntil" => VALID_UNTIL,
                "authUserParam" => "webshop_id"
            ]
        );
        $validationResult2 = SKA\validateSignedRequestData(
            $signatureDict2,
            SECRET_KEY,
            [
                "authUserParam" => "webshop_id"
            ]
        );
        self::assertFalse($validationResult2);

        // Test case 3 - valid non-expired signature as object
        $validationResult3 = SKA\validateSignedRequestData(
            $signatureDict,
            SECRET_KEY,
            [
                "authUserParam" => "webshop_id"
            ],
            true
        );
        self::assertTrue($validationResult3->result);
        self::assertEmpty($validationResult3->errors);

        // Test case 4 - expired signature as object
        $validationResult4 = SKA\validateSignedRequestData(
            $signatureDict2,
            SECRET_KEY,
            [
                "authUserParam" => "webshop_id"
            ],
            true
        );
        self::assertFalse($validationResult4->result);
        self::assertNotEmpty($validationResult4->errors);
        self::assertContains(SIGNATURE_TIMESTAMP_EXPIRED, $validationResult4->errors);
        self::assertNotContains(INVALID_SIGNATURE, $validationResult4->errors);

        // Test case 5 - invalid signature as object
        $signatureDict5 = unserialize(serialize($signatureDict2));
        $signatureDict5[SKA\DEFAULT_SIGNATURE_PARAM] = 'invalid-signature';
        $validationResult5 = SKA\validateSignedRequestData(
            $signatureDict5,
            SECRET_KEY,
            [
                "authUserParam" => "webshop_id"
            ],
            true
        );
        self::assertFalse($validationResult5->result);
        self::assertNotEmpty($validationResult5->errors);
        self::assertContains(SIGNATURE_TIMESTAMP_EXPIRED, $validationResult5->errors);
        self::assertContains(INVALID_SIGNATURE, $validationResult5->errors);

        // Test case 6
        $validUntil6 = SKA\makeValidUntil();
        $signatureDict6 = SKA\signatureToDict(
            AUTH_USER,
            SECRET_KEY,
            ["three" => [], "four" => ["one" => "1"], "five" => "5"],
            [
                "validUntil" => $validUntil6,
                "valueDumper" => SKA\JAVASCRIPT_VALUE_DUMPER
            ]
        );
        $signature6 = $signatureDict6["signature"];
        $authUser6 = AUTH_USER;
        $dataJSON6 = <<<EOT
            {
                "signature": "$signature6",
                "auth_user": "$authUser6",
                "valid_until": "$validUntil6.",
                "extra": "four,three,five",
                "three": {},
                "four": {"one": "1"},
                "five": "5"
            }
EOT;

        $signatureDict6 = json_decode($dataJSON6, true);

        $validationResult6 = SKA\validateSignedRequestData(
            $signatureDict6,
            SECRET_KEY,
            [
                "valueDumper" => SKA\JAVASCRIPT_VALUE_DUMPER,
            ]
        );
        self::assertTrue($validationResult6);
    }

    public function testGenerateSignatureWhenLocaleIsSet(): void
    {
        $validUntil = SKA\makeValidUntil();
        $signature = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            $validUntil,
            SKA\SIGNATURE_LIFETIME,
            PAYLOAD
        );

        setlocale(LC_ALL, 'nl_NL');
        $localeAffectedSignature = SKA\generateSignature(
            AUTH_USER,
            SECRET_KEY,
            $validUntil,
            SKA\SIGNATURE_LIFETIME,
            PAYLOAD
        );

        self::assertEquals($signature->signature, $localeAffectedSignature->signature);
    }
}
