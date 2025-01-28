<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest;

use GuzzleHttp\Psr7\Request;
use LLegaz\ZeltyPhpTest\Handlers\ContentTypeMiddleware;
use LLegaz\ZeltyPhpTest\Handlers\HttpAuthenticationHandler;
use LLegaz\ZeltyPhpTest\Handlers\HttpErrorHandler;
use LLegaz\ZeltyPhpTest\Handlers\ShutdownHandler;
use LLegaz\ZeltyPhpTest\Repositories\UsersRepository;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

use function register_shutdown_function;

/*
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
return function (App $app, Request $request) {
    /**
     * Errors handling display.
     */
    $displayErrorDetails = true;

    $callableResolver = $app->getCallableResolver();
    $responseFactory  = $app->getResponseFactory();

    // Create Error Handler
    $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
    $errorHandler->forceContentType('application/json');

    // Set a custom Error Handler in place of the "shutdownHandler"
    /* restore_error_handler();
      set_error_handler(
      function ($errNo, $errStr, $errFile, $errLine, array $errContext) {
      throw new \Exception($errStr, 500);
      },
      \E_ALL
      ); */

    // Create Shutdown Handler
    $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
    register_shutdown_function($shutdownHandler);

    // Add Misc Middleware(s)
    $app->addBodyParsingMiddleware();
    $app->addMiddleware(new ContentTypeMiddleware());
    $app->addMiddleware(new ContentLengthMiddleware());

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Add Error Handling Middleware
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);

    // Add Security Middleware(s)    (End of Stack = executed first)
    $app->addMiddleware(
        new HttpAuthenticationHandler(
            $app->getContainer(),
            $errorHandler,
            $displayErrorDetails,
            new UsersRepository($app->getContainer()->get(\Doctrine\ORM\EntityManager::class))
        )
    );
};
