<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LLegaz\ZeltyPhpTest\Controllers\AppController;
use LLegaz\ZeltyPhpTest\Controllers\ArticleController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

use function preg_match;

/*
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response): Response {
        // CORS Pre-Flight OPTIONS Request Handler for front-end FETCH
        switch (true) {
            // case for article(s) endpoints
            case preg_match(
                '/^\/article(\/[a-zA-Z-0-9]+){0,1}(\/)?$/U',
                $request->getUri()->getPath()
            ):
                return $response->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withAddedHeader('Access-Control-Allow-Headers', 'Content-type')
                    ->withAddedHeader('Access-Control-Allow-Methods', 'POST, PATCH, GET, DELETE')
                ;
            case '/articles/' === $request->getUri()->getPath():
            case preg_match(
                // match URI like "/articles?page=3&renderHtml=yes&filters=display_name%20LIKE%20zelty"
                '/^\/articles((\/)?(\?){1}(([a-zA-Z]{1,10})(\=){1}([a-z_A-Z%0-9]{1,130})(\&)?){1,4})?$/U',
                $request->getUri()->getPath()
            ):
                return $response->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withAddedHeader('Access-Control-Allow-Methods', 'GET')
                ;
                // other endpoints' cases
            case '/login' === $request->getUri()->getPath():
                return $response->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withAddedHeader('Access-Control-Allow-Headers', 'Content-type')
                    ->withAddedHeader('Access-Control-Allow-Methods', 'POST')
                ;

            default: return $response;
        }
    });

    $app->get('/', [AppController::class, 'root']);
    $app->post('/login', [AppController::class, 'login']);
    $app->get('/login', [AppController::class, 'login']);

    $app->get('/articles[/{params:.*}]', [ArticleController::class, 'readAll']);
    $app->group('/article', function (Group $group) {
        $group->post('[/]', [ArticleController::class, 'create']);
        $group->get('/{id:[a-zA-Z-0-9]+}[/]', [ArticleController::class, 'read']);
        $group->patch('/{id:[a-zA-Z-0-9]+}[/]', [ArticleController::class, 'update']);
        $group->delete('/{id:[a-zA-Z-0-9]+}[/]', [ArticleController::class, 'delete']);
    });
};
