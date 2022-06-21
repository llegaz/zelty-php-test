<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest;

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use LLegaz\ZeltyPhpTest\Authentication\AuthenticationManager;
use LLegaz\ZeltyPhpTest\Authentication\TokenStore\TokenStore;
use LLegaz\ZeltyPhpTest\Utils as ZU;
use PDO;
use Psr\Container\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as VarDumper;
use Throwable;

/*
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'auth' => \DI\factory(
            function (ContainerInterface $c) {
                return new AuthenticationManager($c);
            }
        ),
        'auth-token' => \DI\factory(
            function (ContainerInterface $c) {
                return new TokenStore($c);
            }
        ),
        'db-path' => APP_ROOT . '/var/db/phpsqlite.db',
        'cache'   => function () {
            throw new Exception('Maybe we should use Redis data store');
        },
        'debug' => [
            'cloner' => \DI\factory(
                function (ContainerInterface $c) {
                    return new VarCloner();
                }
            ),
            'dumper' => \DI\factory(
                function (ContainerInterface $c) {
                    return 'cli' === PHP_SAPI ? new CliDumper() : new VarDumper();
                }
            ),
        ],
        // RFC 7616 (Custom implementation)
        'digest-scheme' => [
            'realm' => 'zelty.fr',
            'qop'   => 'auth',
            // A nonce might, for example, be constructed as the Base64 encoding of
            // timestamp H(timestamp ":" ETag ":" secret-data)
            'nonce'  => null, // generate nonce
            'opaque' => null, // generate a salt
        ],
        EntityManager::class => \DI\factory(
            function (ContainerInterface $c) {
                try {
                    return ZU::EntityManagerFactory($c->get('db-path'));
                } catch (Throwable $t) {
                    return ZU::EntityManagerOnErrorFactory($t);
                }
            }
        ),
        PDO::class => \DI\factory(
            function (ContainerInterface $c) {
                try {
                    return ZU::PDOobjectFactory($c->get('db-path'));
                } catch (Throwable $t) {
                    return ZU::PDOobjectOnErrorFactory($t);
                }
            }
        ),
    ]);
};
