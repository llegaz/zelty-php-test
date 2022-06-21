<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers;

use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Slim\Exception\HttpBadRequestException;
use function is_string;
use function preg_match;
use function strlen;
use function strtotime;

/**
 * Validation Helper Class
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class StringValidator
{
    /**
     * @param type        $input
     * @param string|null $arg   the tested argument name for explicit errors messages
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateString(?Request $request, $input, ?string $arg = null): string
    {
        if (!is_string($input)) {
            self::throwException($request, 'argument must be a string.', $arg);
        } elseif (!strlen($input)) {
            self::throwException(
                $request,
                'argument must be a non-empty string !',
                $arg
            );
        }

        return $input;
    }

    /**
     * @param type        $input
     * @param string|null $arg   the tested argument name for explicit errors messages
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateConstrainedString(?Request $request, $input, ?string $arg = null, int $maxLen = 32, int $minLen = 1): string
    {
        self::validateString($request, $input, $arg);

        if (strlen($input) > $maxLen) {
            self::throwException(
                $request,
                'argument is too long (' . $maxLen . ' characters allowed).',
                $arg
            );
        } elseif (strlen($input) < $minLen) {
            self::throwException(
                $request,
                'argument is too short (' . $minLen . ' characters allowed).',
                $arg
            );
        }

        return $input;
    }

    /**
     * Login string is alphanumeric string or an email.
     *
     * @param type        $input
     * @param string|null $arg   the tested argument name for explicit errors messages
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateLoginString(?Request $request, $input, string $arg = 'login'): string
    {
        self::validateConstrainedString($request, $input, $arg);

        if (preg_match('/[^\_\.\@a-zA-Z\d]/', $input)) {
            self::throwException($request, 'argument contains invalid characters.', $arg);
        }

        return $input;
    }

    /**
     * Password is everything but white spaces
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validatePasswordString(?Request $request, $input, string $arg = 'password'): string
    {
        self::validateConstrainedString($request, $input, $arg, 32, 8);

        if (preg_match('/[^\S]/', $input)) {
            self::throwException($request, 'argument contains invalid characters (white space).', $arg);
        }

        return $input;
    }

    /**
     * Some strings won't be escaped (see Utils::sanitizeForHtml5Client)
     * typically users' display names and articles' titles
     * Article content should be escaped but won't except explicitly asked by client.
     *
     * this is draft should be improved
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateConstrainedUnescapedString(?Request $request, $input, ?string $arg = null, int $maxLen = 32): string
    {
        self::validateConstrainedString($request, $input, $arg, $maxLen);

        if (preg_match('/[^\p{L}\d\s\.\'\_\-]/', $input)) {
            self::throwException($request, 'argument contains invalid characters.', $arg);
        }

        return $input;
    }

    /**
     * Typically, Hexadecimal represented strings
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateAlphaNumString(?Request $request, $input, ?string $arg = null, int $maxLen = 64): string
    {
        self::validateConstrainedString($request, $input, $arg, $maxLen);

        if (preg_match('/[^\p{L}\d]/', $input)) {
            self::throwException($request, 'argument contains invalid characters.', $arg);
        }

        return $input;
    }

    /**
     * @param type $input
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    public static function validateDateString(?Request $request, $input, string $arg = 'publishedOn'): string
    {
        self::validateConstrainedString($request, $input, $arg);

        if (false === strtotime($input)) {
            self::throwException($request, 'argument format is invalid (see PHP DateTime object formats).', 'Date: ' . $arg);
        }

        return $input;
    }

    /**
     * @param type $message
     *
     * @throws HttpBadRequestException
     * @throws InvalidArgumentException
     */
    private static function throwException(?Request $request, $message, ?string $arg = null): void
    {
        $message = ($arg ? $arg . ' ' : '') . $message;

        if ($request instanceof Request) {
            throw new HttpBadRequestException($request, $message);
        }

        throw new InvalidArgumentException($message);
    }
}
