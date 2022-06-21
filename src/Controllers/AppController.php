<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Controllers;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use LLegaz\ZeltyPhpTest\Entities\User;
use LLegaz\ZeltyPhpTest\Helpers\JsonHelper as JH;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class AppController extends BaseController
{
    public function __construct(
        ?Container $container
    ) {
        parent::__construct($container);
    }

    public function root(Request $request, Response $response): Response
    {
        return parent::root($request, $response);
    }

    /**
     * Custom HTTP Digest Authentication (RFC 7616, hash algo forced to Argon2id),
     * <b>WILL</b> return an AUTH token to User (RFC 6750 Auth Bearer), valid 1H,
     * for later API usage and thus limit hashed signatures calculations overhead.
     */
    public function login(Request $request, Response $response, ?User $user = null): Response
    {
        $data = ['token' => null];
        // à voir si on garde le state après un redirect
        if ($user && $this->container->get('auth')->hasValidateChallenge()) {
            // create new token per user
            $data['token'] = $this->container->get('auth-token')->create($user);
        }

        return JH::sendJsonResponse($request, $response, $data);
    }
}
