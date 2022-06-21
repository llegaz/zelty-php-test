<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\DevTools;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
$container = require __DIR__ . '/bootstrapper.php';

use LLegaz\ZeltyPhpTest\Utils as ZU;
use PDO;
use Throwable;

try {
    /**
     * some DEBUG utilities.
     */
    $dumper  = $container->get('debug')['dumper'];
    $cloner  = $container->get('debug')['cloner'];
    $results = $container
        ->get(PDO::class)
        //->query('SELECT title, published_on, users.display_name FROM articles LEFT JOIN users ON articles.author_id = users.user_id')
        ->query('SELECT * FROM users')
        ->fetchAll()
    ;

    foreach ($results as $users) {
        $dumper->dump($cloner->cloneVar($users));
    }
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
