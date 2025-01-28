<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers;

use DI\Container;
use GuzzleHttp\Psr7\Request;
use LLegaz\ZeltyPhpTest\Entities\User;
use LLegaz\ZeltyPhpTest\Repositories\UsersRepository;
use Slim\Exception\HttpBadRequestException;

use function is_string;
use function strlen;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class UserValidator
{
    private UsersRepository $userRepository;

    public function __construct(Container $container)
    {
        $this->userRepository = new UsersRepository($container->get(\Doctrine\ORM\EntityManager::class));
    }

    /**
     * Validate Author by display name or User ID.
     *
     * @throws HttpBadRequestException
     */
    public function validateUser(Request $request, string $user, string $arg = 'author'): User
    {
        if (strlen($user)) {
            $authorUser = $this->userRepository->retrieveUser($user);
        }

        if ($authorUser) {
            return $authorUser;
        }

        throw new HttpBadRequestException(
            $request,
            'This user (' . $arg . ': ' . $user . ') does not seem to belong to database'
        );
    }

    public function withoutValidateUser(?string $user = null, string $arg = 'author'): ?User
    {
        if (is_string($user) && strlen($user)) {
            return $this->userRepository->retrieveUser($user);
        }

        return null;
    }
}
