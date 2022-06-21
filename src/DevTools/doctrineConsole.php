<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\DevTools;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
$container = require __DIR__ . '/bootstrapper.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use LLegaz\ZeltyPhpTest\Utils as ZU;
use Throwable;

try {
    $em = $container->get(EntityManager::class);

    $helper = ConsoleRunner::createHelperSet($em);
    ConsoleRunner::run($helper);
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
