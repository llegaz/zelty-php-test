<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Authentication\TokenStore;

use LLegaz\ZeltyPhpTest\Entities\Token;
use LLegaz\ZeltyPhpTest\Entities\User;

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
 *  "hardened".
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
interface TokenStoreInterface
{
    /**
     * return token as string base64 encoded, associated to a given user.
     *
     * @param User
     */
    public function create(User $user): ?string;

    public function read(string $tokenId): ?Token;

    public function revoke(Token $token): void;

    public function revokeExpired(): void;
}
