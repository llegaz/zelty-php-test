<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Tests;

/*
 * @author Matthieu Napoli
 * @link http://en.mnapoli.fr/doctrine-schema-validation-in-a-phpunit-test/
 * @license WTFPL - Do What The Fuck You Want To Public License (http://sam.zoy.org/wtfpl/)
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaValidator;
use function count;
use function implode;

/**
 * Doctrine schema validation.
 *
 * @internal
 */
class SchemaValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     *DEBUG.
     */
    protected $dumper;
    protected $cloner;

    public function setUp(): void
    {
        $container           = require __DIR__ . '/bootstrapper.php';
        $this->entityManager = $container->get(EntityManager::class);
        $this->dumper        = $container->get('debug')['dumper'];
        $this->cloner        = $container->get('debug')['cloner'];
    }

    public function testValidateSchema()
    {
        $validator = new SchemaValidator($this->entityManager);
        $errors    = $validator->validateMapping();
        // $this->dumper->dump($this->cloner->cloneVar($errors));

        if (count($errors) > 0) {
            $message = PHP_EOL;

            foreach ($errors as $class => $classErrors) {
                $message .= '- ' . $class . ':' . PHP_EOL . implode(PHP_EOL, $classErrors) . PHP_EOL . PHP_EOL;
            }
            $this->fail($message);
        }
        // All good(s) ends
        $this->assertEquals(count($errors), 0);
    }
}
