<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Authentication;

/**
 * @see AUTHENTICATION.md
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
interface AuthDigestInterface
{
    public function generateClientNonce(): string;

    public function getA1Hash(string $login, string $password): string;

    public function getA2Hash(string $method = 'POST', string $uri = '/login'): string;

    public function getKeyedDigest(string $A1, string $A2, string $nonce, string $nc, string $cnonce, bool $binary = false): string;

    public function validateChallenge(string $digest): bool;

    public function setAuthScheme(): array;

    public function setSalt(?string $salt = null): self;
}
