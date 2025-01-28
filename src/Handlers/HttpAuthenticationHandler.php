<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Handlers;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use LLegaz\ZeltyPhpTest\Controllers\AppController;
use LLegaz\ZeltyPhpTest\Helpers\InputValidator;
use LLegaz\ZeltyPhpTest\Repositories\UsersRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

use function array_combine;
use function explode;
use function str_replace;
use function substr;
use function trim;

class HttpAuthenticationHandler implements MiddlewareInterface
{
    use ErrorTrait;

    /**
     * @var DI\Container
     */
    private Container $container;

    /**
     *
     * @var HttpErrorHandler
     */
    private HttpErrorHandler $errorHandler;

    /**
     * @var bool
     */
    private $errDetails;

    /**
     * @var string
     */
    private string $authHeaderStart;

    /**
     * @var string
     */
    private string $authorization;

    /**
     *
     * @var UsersRepository
     */
    private UsersRepository $userRepository;

    /**
     * @param HttpErrorHandler $errorHandler
     */
    public function __construct(
        Container $container,
        HttpErrorHandler $errorHandler,
        bool $displayErrorDetails,
        UsersRepository $userRepository
    ) {
        $this->container      = $container;
        $this->errorHandler   = $errorHandler;
        $this->errDetails     = $displayErrorDetails;
        $this->userRepository = $userRepository;
    }

    /**
     * @author Laurent LEGAZ <laurent@legaz.eu>
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            // no auth on OPTIONS requests
            return $handler->handle($request);
        }

        if (!$request->getHeader('Authorization')) {
            return $this->unauthorized($request);
        }
        $this->authorization   = $request->getHeader('Authorization')[0];
        $this->authHeaderStart = substr($this->authorization, 0, 6);

        if ($this->authHeaderStart
                && 'Digest' !== $this->authHeaderStart
                && 'Bearer' !== $this->authHeaderStart
        ) {
            return $this->unauthorized($request);
        } elseif ($this->authHeaderStart === 'Bearer') {
            // check authorization header for token
            $tokenId = str_replace('Bearer ', '', $this->authorization);
            // check token with token store if exist
            $token = $this->container->get('auth-token')->read($tokenId);

            if ($token !== null && !$token->isExpired()) {
                // token is valid
                return $handler->handle($request);
            } elseif ($token !== null && $token->isExpired()) {
                $this->container->get('auth-token')->revoke($token);

                /**
                 *
                 * on Bearer scheme unauthorized flow we could be more compliant by
                 * sending WWW-authenticate for bearer's specific errors  (see
                 *  https://datatracker.ietf.org/doc/html/rfc6750#section-3).
                 */
                return $this->unauthorized($request, $tokenId . ': token has expired.');
            }

            // but if token is not valid, we only respond  with a "401 Unauthorized"
            // status code and a WWW-Authenticate header field against Digest scheme
            return $this->unauthorized($request, $tokenId . ': token is not valid.');
        } elseif ($this->authHeaderStart === 'Digest') {
            $keys   = [];
            $values = [];
            $parts  = explode(',', str_replace('Digest', '', $this->authorization));

            foreach ($parts as $part) {
                $ok       = explode('=', $part);
                $keys[]   = trim($ok[0]);
                $values[] = str_replace('"', '', $ok[1]);
            }
            $authPayload = array_combine($keys, $values);
            // check nonce
            $nonce = InputValidator::validateInputHexString($request, $authPayload, 'nonce');

            if (!$this->container->get('auth')->checkNonce($nonce)) {
                return $this->unauthorized($request, 'Nonce is not valid.');
            }
            // authenticate user Authorization request
            $login   = InputValidator::validateLogin($request, $authPayload);
            $pwdHash = $this->userRepository->getUserA1PasswordHash($login);

            if (null == $pwdHash) {
                throw new HttpBadRequestException($request, 'Wrong login.');
            }
            $cnonce   = InputValidator::validateInputHexString($request, $authPayload, 'cnonce');
            $nc       = InputValidator::validateInputHexString($request, $authPayload, 'nc');
            $response = InputValidator::validateInputHexString($request, $authPayload, 'response');
            $uri      = InputValidator::validateInputString($request, $authPayload, 'uri');
            unset($authPayload);
            $this->container->get('auth')->getKeyedDigest(
                $pwdHash,
                $this->container->get('auth')->getA2Hash('POST'/*$request->getMethod()*/, $uri), // fixed for this 1st version
                $nonce,
                $nc,
                $cnonce
            );

            if ($this->container->get('auth')->validateChallenge($response)) {
                // expires used nonce
                $this->container->get('auth')->expireNonce($nonce);

                // redirect to /login for authenticated user to get its token
                return (new AppController($this->container))->login(
                    $request,
                    new Response(),
                    $this->userRepository->retrieveLastUser()
                );
            }

            return $this->unauthorized($request, 'Digest is not valid.');
        }
    }

    /**
     * Despite the name it's to signal clients that they have to authenticate.
     */
    private function unauthorized(ServerRequestInterface $request, ?string $reason = null): ResponseInterface
    {
        $exception = $reason ? new HttpUnauthorizedException($request, $reason) : new HttpUnauthorizedException($request);
        // this should be logged but... it's a lil app
        $response = $this->errorHandler->__invoke($request, $exception, $this->errDetails, false, false); // ¯\_(ツ)_/¯

        return $this->setJsonHeadersForErrors($response->withHeader('WWW-Authenticate', $this->container->get('auth')->setAuthScheme()));
    }
}
