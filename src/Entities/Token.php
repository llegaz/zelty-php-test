<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Entities;

use DateTime;

use Doctrine\ORM\Mapping as ORM;

use function is_string;
use function strtotime;
use function time;

/**
 *  @see AUTHENTICATION.md
 *
 *
 *  TokenStore inspired from  Neil Madden - API Security in Action
 *  for token based authentication (once authentication is realized
 *  a temporary token is issued to User and it could be used with
 *  Authorization: Bearer classic header (RFC 6750).
 *
 *  Here for the sake of the exercise, I will use SQLITE to store token.
 *  In real life a REDIS data store should be privileged. And tokens won't be
 *  "hardened" (signed or hashed).
 *
 *
 *  In real life, <b>tokens (token_id) SHOULD BE hardened</b> 1/ hashed using
 *  fast and reliable (secure enough) HASH Algorithm (e.g SHA-256) in order to
 *  avoid data thefts (e.g SQL injections). Moreover, HMAC can be used to sign
 *  tokens, with a secure key known by the API. Signatures prevent tampering or
 *  tokens forgery (non authorized DB insertions).
 *
 *  Final word : this work is not JWT, even if it could be elaborated to match JWT
 *  standards. This work is simpler but offers reliability over stateless, client based
 *  JWT implementation (e.g easier revocation for all tokens from database).
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
/**
 * @ORM\Table(name="tokens",indexes={
 *   @ORM\Index(name="idx_tokens", columns={"expiry"})
 * })
 * @ORM\Entity
 */
class Token
{
    /**
     * @ORM\Column(name="token_id", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $tokenId;

    /**
     * @ORM\Column(name="user_id", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $userId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="expiry", type="string", length=32, nullable=false)
     */
    private $expiry;

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setTokenId(string $tokenId): self
    {
        $this->tokenId = $tokenId;

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function isExpired(): bool
    {
        return strtotime($this->getExpiry()) < time();
    }

    public function getExpiry(): string
    {
        if ($this->expiry instanceof DateTime) {
            return $this->expiry->format(DateTime::ATOM);
        }

        return $this->expiry;
    }

    /**
     *
     * @param string|DateTime $expiry
     * @return self
     */
    public function setExpiry($expiry): self
    {
        if (is_string($expiry)) {
            $this->expiry = $expiry;
        } elseif ($expiry instanceof DateTime) {
            $this->expiry = $expiry->format(DateTime::ATOM);
        }

        return $this;
    }
}
