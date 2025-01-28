<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\DevTools;

/**
 * Those operations should be done client but this script is provided for
 * relative "ease of use" during development (i.e could be useful with Postman or cURL).
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
$container = require __DIR__ . '/bootstrapper.php';

use InvalidArgumentException;
use LLegaz\ZeltyPhpTest\Helpers\StringValidator as SV;
use LLegaz\ZeltyPhpTest\Utils as ZU;
use Throwable;

/* * * * * * * * * * * * * * * * * * * * * * * *
 * Returns Authorization header payload like
 *

  Authorization: Digest username="Mufasa", realm="http-auth@example.org", uri="/login",
  nonce="7ypf/xlj9XXwfDPEoM4URrv/xwf94BcCAzFZH4GiTo0v", nc=00000001,
  cnonce="f2/wE4q74E6zIJEtWaHKaf5wv/H5QzzpXusqGemxURZJ", qop=auth,
  response="753927fa0e85d155564e2e272a28d1802ca10daf4496794697cf8db5856cb6c1",

* * * * * * * * * */

const COMMAND_1 = 'auth:client:digest';
const DESCR_1   = 'Return assiociated Authorization header with Authentication Digest response for Zelty PHP Application';
const DESCR_2   = '(warning: there is no check against application database so enter a valid tupple with login, password,';
const DESCR_3   = 'etc. else API authenthication will fail).';

try {
    if (!isset($argv[1]) || '-h' === $argv[1] || '--help' === $argv[1]) {
        // display general help
        ZU::colorYellowToCLI('Usage:');
        ZU::nocolorToCLI('  command [options] [arguments]', ' ');
        ZU::colorYellowToCLI('Options:');
        ZU::commandSymfonyStyleToCLI('-h, --help', 'Display help for the given command. When no command is given display help for the command list');
        ZU::colorYellowToCLI('Available command:');
        ZU::commandSymfonyStyleToCLI(COMMAND_1, DESCR_1);
        ZU::commandSymfonyStyleToCLI(' ', DESCR_2);
        ZU::commandSymfonyStyleToCLI(' ', DESCR_3);

        exit(0);
    }

    if (isset($argv[1]) && COMMAND_1 === $argv[1]) {
        if (!isset($argv[2]) || '-h' === $argv[2] || '--help' === $argv[2]) {
            if ($argc > 3) {
                $argv[2] = $argv[3];
                $argv[3] = $argv[4];
                $argv[4] = $argv[5];
                $argv[5] = $argv[6];
            } else {
                // display command help
                ZU::colorYellowToCLI('Description:');
                ZU::nocolorToCLI('  ' . DESCR_1 . PHP_EOL . '  ' . DESCR_2 . ' ' . DESCR_3 . PHP_EOL);
                ZU::colorYellowToCLI('Usage:');
                ZU::nocolorToCLI('  ' . COMMAND_1 . ' [options] [<login> <password> <nonce> [<opaque>]]' . PHP_EOL);
                ZU::colorYellowToCLI('Arguments:');
                ZU::commandSymfonyStyleToCLI('login', 'The user login without quotes (mandatory)', 18);
                ZU::commandSymfonyStyleToCLI('password', 'The user password without quotes (mandatory)', 18);
                ZU::commandSymfonyStyleToCLI('nonce', 'The nonce value without quotes (mandatory)', 18);
                ZU::commandSymfonyStyleToCLI('opaque', 'The opaque value without quotes (e.g opaque="xxx", where xxx is the opaque value)', 18);
                ZU::colorYellowToCLI(PHP_EOL . 'Help:');
                echo '  The ' . ZU::getGreenColoredString(COMMAND_1) . ' is a tool aiming to help in testing API authentication.' . PHP_EOL;
                echo '  It returns a payload useful with tools like ' . ZU::getGreenColoredString('Postman') . ' or ' . ZU::getGreenColoredString('cURL') . '.' . PHP_EOL . PHP_EOL;
                echo ZU::getGreenColoredString('  nonce') . ' and ' . ZU::getGreenColoredString('opaque');
                echo ' arguments are the quoted value present in ';
                echo ZU::getGreenColoredString('WWW-Authenticate') . ' response header fields.' . PHP_EOL;
                echo '  Last but not least: pass all those arguments' . ZU::getGreenColoredString(' UNQUOTED !') . PHP_EOL;
                echo PHP_EOL . '  Example usage: ' . ZU::getGreenColoredString(COMMAND_1);
                echo ' admin admin_password dbad2031bb412e07dbe547129b370332a6a940659d114b9e c9be4be4a64cea86a1c625f5aab293f3' . PHP_EOL . PHP_EOL;

                exit(0);
            }
        }

        if (!isset($argv[2])) {
            throw new InvalidArgumentException('missing credential (login)');
        }
        $username = SV::validateLoginString(null, $argv[2]);

        if (!isset($argv[3])) {
            throw new InvalidArgumentException('missing credential (password)');
        }
        $password = SV::validateConstrainedString(null, $argv[3], 'password', 32, 8);

        if (!isset($argv[4])) {
            throw new InvalidArgumentException('missing nonce');
        }
        $nonce = SV::validateAlphaNumString(null, $argv[4], 'nonce');

        if (isset($argv[5])) {
            $opaque = SV::validateAlphaNumString(null, $argv[5], 'opaque');
        } else {
            $opaque = $container->get('auth')->retrieveOpaque(true);

            if (null === $opaque) {
                ZU::colorRedToCLI('Warning: no users in DB, please import some users first with importDataFixtures.php');
            }
        }
        ZU::colorGreenToCLI('Authorization:');
        $nc     = '00000001';
        $cnonce = $container->get('auth')->generateClientNonce();

        /**
         * For some "ease" in development and "conviviality" in usage,
         * Method and URI arguments are fixed...
         * But, of course, it could be implemented easily server-side
         *
         * Finally that project is more like a PoC and none of this matter without
         * a mirrored API client able to respond to the custom HTTP Digest challenge
         * implemented here...
         */
        ZU::colorYellowToCLI(
            'Digest username="' . $username . '", realm="' .
                $container->get('digest-scheme')['realm'] . '", uri="/login", qop="' .
                $container->get('digest-scheme')['qop'] . '", nonce="' . $nonce .
                '", nc=' . $nc . ', cnonce="' . $cnonce . '", response="' .
                $container->get('auth')->getKeyedDigest(
                    $container->get('auth')->getA1Hash($username, $password),
                    $container->get('auth')->getA2Hash(),
                    $nonce,
                    $nc,
                    $cnonce
                ) . '"'
        );

        exit(0);
    }
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
