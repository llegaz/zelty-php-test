<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class ContentTypeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response->hasHeader('Content-Type')) {
            if ('application/json' !== $response->getHeader('Content-Type')) {
                $response = $response->withoutHeader('Content-Type');
            } else {
                return $response;
            }
        }

        return $response->withAddedHeader('Content-Type', 'application/json');
    }
}
