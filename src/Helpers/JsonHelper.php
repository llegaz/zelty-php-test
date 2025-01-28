<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use function count;
use function json_decode;
use function json_encode;
use function json_last_error;
use function strstr;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class JsonHelper
{
    public static function sendJsonResponse(Request $request, Response $response, array $data): Response
    {
        $count = count($data);

        if ($count) {
            $response->getBody()->write(json_encode($data));

            return $response->withHeader('Content-Type', 'application/json');
        }

        throw new HttpNotFoundException($request, 'Resource(s) requested returned with an empty set');
    }

    public static function handleJsonRequest(Request $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (strstr($contentType, 'application/json')) {
            $body = $request->getBody();
            $body->rewind();
            $data = json_decode($body->getContents(), true);
            $code = json_last_error();

            if ($code > 0 || !count($data)) {
                throw new HttpBadRequestException($request, "Request's JSON body empty or malformed");
            }

            return $data;
        }

        throw new HttpBadRequestException($request, 'Content-Type header is missing, JSON expected');
    }
}
