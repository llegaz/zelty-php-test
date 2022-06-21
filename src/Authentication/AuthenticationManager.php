<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Authentication;

use DI\Container;
use PDO;
use Throwable;
use UnexpectedValueException;
use function bin2hex;
use function hash_hmac;
use function hex2bin;
use function random_bytes;
use function sodium_crypto_pwhash;

/**
 * @see AUTHENTICATION.md
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
final class AuthenticationManager implements AuthDigestInterface
{
    /**
     *
     * @var Container
     */
    private Container $container;

    /**
     *
     * @var string|null
     */
    private ?string $salt = null;

    /**
     * Did user validated Digest challenge
     *
     * @var boolean
     */
    private bool $challengeOk = false;
    private string $digest    = '';

    public function __construct(
        Container $container
    ) {
        $this->container = $container;
    }

    private function generateAndStoreNonce(): string
    {
        $nonce = $this->generateNonce();
        $this->storeNonce($nonce);

        return $nonce;
    }

    /*     * *******    *******
     *  Basic example (RAW HTTP)
     *

      HTTP/1.1 401 Unauthorized
      WWW-Authenticate: Digest
      realm="http-auth@example.org",
      qop="auth",
      nonce="7ypf/xlj9XXwfDPEoM4URrv/xwf94BcCAzFZH4GiTo0v",
      opaque=""

     * ************************ */

    public function setAuthScheme(): array
    {
        // RFC 7616 (Custom implementation)
        return [
            'Digest',
            'realm="' . $this->container->get('digest-scheme')['realm'] . '"',
            'qop="' . $this->container->get('digest-scheme')['qop'] . '"',
            'nonce="' . $this->generateAndStoreNonce() . '"',
            'opaque="' . $this->retrieveOpaque() . '"',
        ];
    }

    public function hasValidateChallenge(): bool
    {
        return $this->challengeOk;
    }

    public function validateChallenge(string $digest): bool
    {
        if ($this->challengeOk) {
            return true;
        } elseif ($digest === $this->digest) {
            $this->challengeOk = true;

            return true;
        }

        return false; //return $this->challengeOk ? true : ($digest === $this->digest);
    }

    public function generateClientNonce(): string
    {
        return $this->generateNonce();
    }

    private function generateNonce(): string
    {
        return bin2hex(random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES));
    }

    private function generateSalt(): string
    {
        return bin2hex(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));
    }

    public function getA1Hash(string $login, string $password): string
    {
        return $this->getHash(
            $login . ':' . $this->container
                ->get('digest-scheme')['realm'] . ':' . $password
        );
    }

    public function getA2Hash(string $method = 'POST', string $uri = '/login'): string
    {
        return $this->getHash($method . ':' . $uri);
    }

    private function getHash(string $toHash): string
    {
        return bin2hex(
            sodium_crypto_pwhash(
                27, // == 216 bits
                $toHash,
                $this->retrieveOpaque(true),
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
            )
        );
    }

    /**
     * response = "HMAC-256( Argon2id(username:realm:passwd), nonce:nc:cnonce:qop:Argon2id(Method:request-uri))"
     *
     *
     * @param string $A1
     * @param string $A2
     * @param string $nonce
     * @param string $nc
     * @param string $cnonce
     * @param bool $binary
     * @return string
     */
    public function getKeyedDigest(string $A1, string $A2, string $nonce, string $nc, string $cnonce, bool $binary = false): string
    {
        $this->digest = hash_hmac(
            'sha256',
            $nonce . ':' . $nc . ':' . $cnonce . ':' . $this->container->get('digest-scheme')['qop'] . ':' . $A2,
            $A1,
            $binary
        );

        return $this->digest;
    }

    private function storeNonce(string $nonce): void
    {
        $this->container->get(PDO::class)
            ->prepare('INSERT INTO nonces (nonce) VALUES (:nonce)')
            ->execute([':nonce' => $nonce])
        ;
    }

    public function checkNonce(string $nonce): bool
    {
        try {
            $stmt = $this->container->get(PDO::class)
                ->prepare('SELECT DISTINCT * FROM nonces WHERE nonce=:nonce')
            ;
            $stmt->execute([':nonce' => $nonce]);
            $result = $stmt->fetch();

            if ($result !== false && isset($result['nonce'])) {
                return $result['nonce'] === $nonce;
            }
        } catch (Throwable $t) {
            return false;
        }

        return false;
    }

    public function expireNonce(string $nonce): void
    {
        $this->container->get(PDO::class)
            ->prepare('DELETE FROM nonces WHERE nonce=:nonce')
            ->execute([':nonce' => $nonce])
        ;
    }

    /**
     * @todo Find a way to not share Argon2id salt
     *       OR implement Digest with SHA-256 in place of Argon
     *       could even just stick with RFC 7616... (without MD5)
     *       OR and not least, if that solution is secure enough, find
     *       a better way to store that salt (e.g Redis)
     */
    public function retrieveOpaque(bool $binary = false): string
    {
        if (null === $this->salt) {
            $this->setSalt();
        }

        if ($binary) {
            return hex2bin($this->salt);
        }

        return $this->salt;
    }

    public function setSalt(?string $salt = null): self
    {
        if ($salt) {
            $this->salt = $salt;
        } else {
            $this->salt = $this->retrieveSalt();
        }

        return $this;
    }

    private function retrieveSalt(): string
    {
        try {
            $salt = $this->container->get(PDO::class)
                ->query('SELECT pwd_salt FROM users LIMIT 1')
                ->fetch()
            ;

            if (isset($salt['pwd_salt'])) {
                $salt = $salt['pwd_salt'];
            } else {
                throw new UnexpectedValueException('no salt yet');
            }
        } catch (Throwable $t) {
            $salt = $this->generateSalt();
        } finally {
            return $salt;
        }
    }
}
