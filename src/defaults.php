<?php
declare(strict_types=1);

namespace SKA;

/**
 * *******************************************
 * *************** Constants *****************
 * *******************************************
 */

/**
 * Signature lifetime in seconds.
 * @var int Constant SIGNATURE_LIFETIME
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
