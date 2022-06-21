<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Exceptions;

use Exception;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class NotSupportedException extends Exception
{
    public function __construct(string $message = 'This feature is not yet supported !')
    {
        parent::__construct($message);
    }
}
