<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers;

use GuzzleHttp\Psr7\Request;
use Slim\Exception\HttpBadRequestException;
use function filter_var;

/**
 * Validation Helper Class
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class BooleanValidator
{
    /**
     * @param mixed|string        $input
     * @param string|null $arg   the tested argument name for explicit errors messages
     *
     * @throws HttpBadRequestException
     */
    public static function validateBool(Request $request, $input, ?string $arg = null): bool
    {
        $bln = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($bln === null) {
            throw new HttpBadRequestException(
                $request,
                ($arg ? $arg . ' ' : '') . 'argument must be a boolean or similar (e.g ON, off, Yes, NO)'
            );
        }

        return $bln;
    }
}
