<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Repositories;

use Doctrine\ORM\EntityManager;
use LLegaz\ZeltyPhpTest\Entities\User;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class UsersRepository extends BaseRepository
{
    private User $lastUser;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);
    }

    public function getUserA1PasswordHash(string $login): ?string
    {
        $this->lastUser = $this->em->getRepository(User::class)->findOneBy(['login' => $login]);

        if ($this->lastUser instanceof User) {
            return $this->lastUser->getPasswordHash();
        }

        return null;
    }

    public function retrieveLastUser(): ?User
    {
        return $this->lastUser;
    }

    public function retrieveUser(string $needle): ?User
    {
        return $this->em->getRepository(User::class)
            ->findOneBy(['displayName' => $needle]) ??
                $this->em->getRepository(User::class)
                    ->findOneBy(['userId' => $needle])
        ;
    }
}
