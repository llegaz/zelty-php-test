<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\DevTools;

/**
 * note: storing DATE as ISO8601 strings.
 *
 * tokenStore inspired from  Neil Madden - API Security in Action
 *
 * PK are CLOB  (https://sqlite.org/forum/info/69c8cbbe2d84410f)
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
$container = require __DIR__ . '/bootstrapper.php';

use LLegaz\ZeltyPhpTest\Utils as ZU;
use PDO;
use Throwable;

ZU::colorGreenToCLI('importing project DB');

/*
 * pwd_hash hash from users table is storing A1 (RFC 7616)
 * i.e  Argon2id(username:realm:passwd) using pwd_salt
 */
try {
    $commands = [
        /*
         'DROP TABLE IF EXISTS articles',
-        'DROP TABLE IF EXISTS users',
-        'DROP TABLE IF EXISTS tokens',
-        'DROP INDEX IF EXISTS idx_articles',
-        'DROP INDEX IF EXISTS idx_users',
-        'DROP INDEX IF EXISTS idx_author',
-        'DROP INDEX IF EXISTS idx_tokens',
         */
        'CREATE TABLE IF NOT EXISTS users (
                        user_id CLOB NOT NULL PRIMARY KEY,
                        display_name VARCHAR(32) NOT NULL,
                        login VARCHAR(32) NOT NULL,
                        pwd_hash CLOB NOT NULL,
                        pwd_salt CLOB NOT NULL
                      )',
        'CREATE TABLE IF NOT EXISTS articles (
                        article_id CLOB NOT NULL PRIMARY KEY,
                        title VARCHAR(128) NOT NULL,
                        published_on VARCHAR(32),
                        state VARCHAR(16) NOT NULL,
                        content CLOB,
                        author_id CLOB,
                        FOREIGN KEY(author_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
                      )',
        // (suggestion: prefer a Redis Data store to cache temporary OT nonces)
        'CREATE TABLE IF NOT EXISTS nonces (
                    nonce CLOB NOT NULL,
                    PRIMARY KEY(nonce)
                    )',
        'CREATE TABLE IF NOT EXISTS tokens (
                    token_id CLOB NOT NULL,
                    user_id CLOB NOT NULL,
                    expiry VARCHAR(32) NOT NULL,
                    FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
                    PRIMARY KEY(token_id, user_id)
                    )',
        'CREATE INDEX IF NOT EXISTS idx_articles ON articles (title)',
        'CREATE INDEX IF NOT EXISTS idx_users ON users (display_name)',
        'CREATE INDEX IF NOT EXISTS idx_author ON articles (author_id)',
        // Theorically, that index could be useful in real world if implemented on RDBMS
        // (suggestion: prefer a Redis Data store for token based authentication)
        'CREATE INDEX IF NOT EXISTS idx_tokens ON tokens (expiry)',
    ];

    foreach ($commands as $command) {
        $container->get(PDO::class)->exec($command);
    }
    $stmt = $container->get(PDO::class)->query("SELECT name
                                   FROM sqlite_master
                                   WHERE type = 'table'
                                   ORDER BY name");

    $dumper = $container->get('debug')['dumper'];
    $dumper->dump($container->get('debug')['cloner']->cloneVar($stmt->fetchAll()));
    ZU::colorGreenToCLI('Great success !');
} catch (Throwable $t) {
    ZU::colorRedToCLI($t->getMessage());
}
