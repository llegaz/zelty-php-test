<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', realpath(__DIR__ . '/../'));
}

require APP_ROOT . '/vendor/autoload.php';

// Should be set to 0 in production
error_reporting(\E_ALL);

// Should be set to '0' in production
ini_set('display_errors', '1');

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
    $containerBuilder->enableCompilation(APP_ROOT . '/var/cache');
}

// Set up services
$services = require APP_ROOT . '/src/services.php';
$services($containerBuilder);

// Build PHP-DI Container instance
$containerBuilder->useAnnotations(false);
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Create Request object from globals
$requestCreator = ServerRequestCreatorFactory::create();
$request        = $requestCreator->createServerRequestFromGlobals();

// Register middleware
$middleware = require APP_ROOT . '/src/middlewares.php';
$middleware($app, $request);

// Register routes
$routes = require APP_ROOT . '/src/routes.php';
$routes($app);

// Run App & Emit Response
$app->run($request);
