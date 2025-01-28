<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Psr7\ServerRequest;
use PDO;
use Slim\Exception\HttpInternalServerErrorException;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Throwable;

use function call_user_func_array;
use function htmlentities;
use function microtime;
use function sprintf;
use function str_pad;
use function str_replace;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class Utils
{
    public const RED_BRACKET_COLOR = "\033[31m";

    public const GREEN_BRACKET_COLOR = "\033[0;32m";

    public const YELLOW_BRACKET_COLOR = "\033[0;33m";

    public const BLUE_BRACKET_COLOR = "\033[1;34m";

    public const NO_COLOR_BRACKET = "\033[0m";

    public static function colorGreenToCLI(string $str): void
    {
        echo self::GREEN_BRACKET_COLOR . $str . self::NO_COLOR_BRACKET . PHP_EOL;
    }

    public static function getGreenColoredString(string $str): string
    {
        return self::GREEN_BRACKET_COLOR . $str . self::NO_COLOR_BRACKET;
    }

    public static function colorRedToCLI(string $str): void
    {
        echo self::RED_BRACKET_COLOR . $str . self::NO_COLOR_BRACKET . PHP_EOL;
    }

    public static function colorBlueToCLI(string $str): void
    {
        echo self::BLUE_BRACKET_COLOR . $str . self::NO_COLOR_BRACKET . PHP_EOL;
    }

    public static function colorYellowToCLI(string $str): void
    {
        echo self::YELLOW_BRACKET_COLOR . $str . self::NO_COLOR_BRACKET . PHP_EOL;
    }

    public static function nocolorToCLI(string $str, ?string $_str = null): void
    {
        echo self::NO_COLOR_BRACKET . $str . self::NO_COLOR_BRACKET . PHP_EOL .
                ($_str ? $_str . PHP_EOL : '');
    }

    public static function commandSymfonyStyleToCLI(string $command, string $str, ?int $pad = 37): void
    {
        echo self::GREEN_BRACKET_COLOR . str_pad('  ' . $command, $pad) . self::NO_COLOR_BRACKET
                . $str . self::NO_COLOR_BRACKET . PHP_EOL;
    }

    /**
     * I choose not to wrap doctrine settings inside PHP DI container.
     * But it could easily be done..
     *
     * (see https://php-di.org/doc/php-definitions.html#factories)
     *
     * @param string sqlite db path
     */
    public static function EntityManagerFactory(
        string $path
    ): EntityManager {
        $settings = [
            'dev_mode'      => true,
            'cache_dir'     => APP_ROOT . '/var/doctrine',
            'metadata_dirs' => [APP_ROOT . '/src/Entities'],
            'connection'    => [
                'driver' => 'pdo_sqlite',
                'path'   => $path,
                'memory' => false,
            ],
        ];
        $cache         = $settings['dev_mode'] ? new ArrayAdapter() : new FilesystemAdapter('', 0, $settings['cache_dir']);
        $cacheProvider = DoctrineProvider::wrap($cache);

        $config = Setup::createAnnotationMetadataConfiguration(
            $settings['metadata_dirs'],
            $settings['dev_mode'],
            null,
            $cacheProvider
        );
        $driver = new AnnotationDriver(new AnnotationReader(), $settings['metadata_dirs']);
        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        return EntityManager::create($settings['connection'], $config);
        //dump($em->getConfiguration()->getMetadataDriverImpl());
    }

    public static function EntityManagerOnErrorFactory(?Throwable $t = null): stdClass
    {
        return self::PDOobjectOnErrorFactory($t);
    }

    /**
     * kek.
     *
     * @param string $name
     *
     * @return array
     */
    public static function PDOobjectFactory(
        string $path
    ): PDO {
        return new PDO(
            'sqlite:' . $path,
            null,
            null,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * kek again.
     */
    public static function PDOobjectOnErrorFactory(?Throwable $t = null): stdClass
    {
        return new class ($t) extends stdClass {
            private $error;

            public function __construct($t)
            {
                $this->error = $t;
            }

            public function __invoke()
            {
                self::throwError();
            }

            public function __call($method, $args)
            {
                self::throwError($this->error->getMessage());
            }

            public static function __callStatic($method, $args)
            {
                self::throwError();
            }

            public static function throwError(string $message = 'SQLite Server not accessible')
            {
                throw new HttpInternalServerErrorException(new ServerRequest('GET', PDO::class), $message);
            }
        };
    }

    public static function sanitizeUrl($url): string
    {
        return str_replace(
            [
                ' ',
            ],
            [
                '%20',
            ],
            $url
        );
    }

    public static function sanitizeForHtml5Client(string $output): string
    {
        return htmlentities($output, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public static function benchmark(callable $callback, array $params = []): string
    {
        $startTime = microtime(true);
        call_user_func_array($callback, $params);
        $endTime = microtime(true);

        return sprintf('%.9f microseconds', $endTime - $startTime);
    }
}
