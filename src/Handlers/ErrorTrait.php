<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Handlers;

use GuzzleHttp\Psr7\Response;

/**
 * Trait implementing functionality common to Errors handling.
 *
 * @todo improve this
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
trait ErrorTrait
{
    public function setJsonHeadersForErrors(Response $response): Response
    {
        if (!$response->hasHeader('Content-type')) {
            $response = $response->withHeader('Content-type', 'application/json');
        }
        $size = $response->getBody()->getSize();

        if (null !== $size) {
            $response = $response->withHeader('Content-Length', (string) $size);
        }

        return $response;
    }
}
