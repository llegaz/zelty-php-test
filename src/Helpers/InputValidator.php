<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers;

use GuzzleHttp\Psr7\Request;
use LLegaz\ZeltyPhpTest\Helpers\StringValidator as SV;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotImplementedException;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class InputValidator
{
    /**
     * @throws HttpBadRequestException
     */
    public static function validateInput(Request $request, array $inputs, string $arg): void
    {
        if (!isset($inputs[$arg])) {
            throw new HttpBadRequestException($request, $arg . ' ' . ' argument is missing');
        }
    }

    /**
     * @throws HttpBadRequestException
     */
    public static function validateInputHexString(Request $request, array $inputs, string $arg): string
    {
        self::validateInput($request, $inputs, $arg);

        return SV::validateAlphaNumString($request, $inputs[$arg], $arg);
    }

    public static function validateInputString(Request $request, array $inputs, string $arg): string
    {
        self::validateInput($request, $inputs, $arg);

        return SV::validateConstrainedString($request, $inputs[$arg], $arg);
    }

    /**
     * @throws HttpBadRequestException
     */
    public static function validateLogin(Request $request, array $inputs, string $arg = 'username'): string
    {
        self::validateInput($request, $inputs, $arg);

        return SV::validateLoginString($request, $inputs[$arg], $arg);
    }

    /**
     * @throws HttpBadRequestException
     */
    public static function validatePassword(Request $request, array $inputs, string $arg = 'password', bool $validatePwdRules = false): string
    {
        self::validateInput($request, $inputs, $arg);

        if ($validatePwdRules) {
            // implement password validation rules policy here (must contain digit, upper and lower letter, special chars, etc.)
            throw new HttpNotImplementedException($request, 'Password polices are not implemented.');
        }

        return SV::validatePasswordString($request, $inputs[$arg]);
    }
}
