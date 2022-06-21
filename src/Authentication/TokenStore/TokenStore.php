<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Authentication\TokenStore;

use DateInterval;
use DateTime;
use DI\Container;
use Doctrine\ORM\EntityManager;
use LLegaz\ZeltyPhpTest\Entities\Token;
use LLegaz\ZeltyPhpTest\Entities\User;
use Throwable;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function function_exists;
use function openssl_random_pseudo_bytes;
use function random_bytes;

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
final class TokenStore implements TokenStoreInterface
{
    /**
     *
     * @var Container
     */
    private Container $container;

    /**
     *
     * @var EntityManager
     */
    private EntityManager $em;

    public const TOKEN_LENGTH = 22;

    public function __construct(
        Container $container
    ) {
        $this->container = $container;
        $this->em        = $container->get(EntityManager::class);
    }

    /**
     * return token as string base64 encoded, associated to a given user.
     *
     * @param User
     */
    public function create(User $user): ?string
    {
        try {
            $token   = new Token();
            $tokenId = $this->generateRandomTokenWithEntropy();
            $token
                ->setUserId($user->getUserId())
                ->setTokenId($tokenId)
                ->setExpiry((new DateTime())->add(new DateInterval('PT1H'))) // expires in 1H
            ;
            $this->em->persist($token);
            $this->em->flush();
        } catch (Throwable $t) {
            //dump($t->getMessage());
            $tokenId = null;
        } finally {
            return base64_encode($tokenId);
        }
    }

    /**
     * retrieve token entity from a base64 encoded token id.
     *
     * @param string base64 tokenId
     * @return Token|null associated token entity
     */
    public function read(string $tokenId): ?Token
    {
        $decodedTokenId = base64_decode($tokenId, true);

        if ($decodedTokenId !== false) {
            return $this->em->getRepository(Token::class)->findOneBy(
                ['tokenId' => $decodedTokenId]
            );
        }

        return null;
    }

    public function revoke(Token $token): void
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    public function revokeAll(Token $token): void
    {
        // not implemented
    }

    public function revokeExpired(): void
    {
        // not implemented
    }

    private function generateRandomTokenWithEntropy(): string
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(self::TOKEN_LENGTH);
        } else {
            $bytes = random_bytes(self::TOKEN_LENGTH);
        }

        return bin2hex($bytes);
    }
}
