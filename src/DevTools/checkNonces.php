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

use function count;

try {
    /**
     * some DEBUG utilities.
     */
    $results = $container
        ->get(PDO::class)
        ->query('SELECT * FROM nonces')
        ->fetchAll()
    ;

    if (count($results)) {
        ZU::colorYellowToCLI('-----------------  Nonces  -----------------');

        foreach ($results as $nonces) {
            foreach ($nonces as $nonce) {
                ZU::colorBlueToCLI($nonce);
            }
        }
        ZU::colorYellowToCLI('-----------------  Nonces  -----------------');
    }
    $tokenResults = $container
        ->get(PDO::class)
        ->query('SELECT * FROM tokens')
        ->fetchAll()
    ;

    if (count($tokenResults)) {
        ZU::colorYellowToCLI('-----------------  Tokens  -----------------');

        foreach ($tokenResults as $tokens) {
            $toPrint = '';

            foreach ($tokens as $token) {
                $toPrint .= $token;
                $toPrint .= ' - ';
            }
            ZU::colorGreenToCLI($toPrint);
        }
        ZU::colorYellowToCLI('-----------------  Tokens  -----------------');
    }
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
