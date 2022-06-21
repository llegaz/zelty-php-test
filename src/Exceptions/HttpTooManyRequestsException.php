<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class HttpTooManyRequestsException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 522;

    /**
     * @var string
     */
    protected $message     = 'Too Many Requests.';
    protected $title       = '522 Too Many Requests';
    protected $description = 'You have requested this endpoint URL too many times. Please, retry in a moment.';
}
