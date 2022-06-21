<?php

declare(strict_types=1);

use DI\ContainerBuilder;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
if (!defined('APP_ROOT')) {
    define('APP_ROOT', realpath(__DIR__ . '/../../'));
}

require APP_ROOT . '/vendor/autoload.php';

error_reporting(\E_ALL);
ini_set('display_errors', '1');
$containerBuilder = new ContainerBuilder();
$services         = require APP_ROOT . '/src/services.php';
$services($containerBuilder);
$containerBuilder->useAnnotations(false);

return $containerBuilder->build();
