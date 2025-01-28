<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Handlers;

use Exception;
use LLegaz\ZeltyPhpTest\Exceptions\HttpTooManyRequestsException;
use LLegaz\ZeltyPhpTest\Exceptions\NotSupportedException;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Throwable;

use function count;
use function explode;
use function json_encode;
use function strcmp;
use function strlen;

class HttpErrorHandler extends ErrorHandler
{
    use ErrorTrait;

    public const BAD_REQUEST = 'BAD_REQUEST';

    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';

    public const NOT_ALLOWED = 'NOT_ALLOWED';

    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';

    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';

    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public const SERVER_ERROR = 'SERVER_ERROR';

    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    protected function respond(): ResponseInterface
    {
        $exception   = $this->exception;
        $statusCode  = 500;
        $type        = self::SERVER_ERROR;
        $message     = '';
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode  = $exception->getCode();
            $message     = $exception->getMessage();
            $description = strlen($exception->getDescription()) > 0 ? $exception->getDescription() : $description;

            if ($exception instanceof HttpNotFoundException) {
                $type = self::RESOURCE_NOT_FOUND;
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpForbiddenException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            } elseif ($exception instanceof HttpTooManyRequestsException) {
                $type = self::TOO_MANY_REQUESTS;
            } elseif ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        } elseif ($exception instanceof NotSupportedException) {
            $type       = self::NOT_IMPLEMENTED;
            $statusCode = 501;
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $description = $exception->getMessage();
            $stackTrace  = $exception->getTraceAsString();
        }

        $arrDescription = explode(PHP_EOL, $description);
        $error          = [
            'statusCode' => $statusCode,
            'error'      => [
                'type'        => $type,
                'description' => count($arrDescription) > 1 ? $arrDescription : $description,
            ],
        ];

        if ($this->displayErrorDetails && strlen($message)
                && 0 !== strcmp($description, $message)) {
            $error['error']['message'] = $message;
        }

        if (isset($stackTrace)) {
            $arrStackTrace                 = explode(PHP_EOL, $stackTrace);
            $error['error']['stack trace'] = count($arrStackTrace) > 1 ? $arrStackTrace : $stackTrace;
        }

        $payload  = json_encode($error, JSON_PRETTY_PRINT);
        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($payload);

        return $this->setJsonHeadersForErrors($response);
    }
}
