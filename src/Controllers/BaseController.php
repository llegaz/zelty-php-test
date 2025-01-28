<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Controllers;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use LLegaz\ZeltyPhpTest\Entities\User;
use LLegaz\ZeltyPhpTest\Helpers\InputValidator as IV;
use LLegaz\ZeltyPhpTest\Helpers\JsonHelper as JH;
use LLegaz\ZeltyPhpTest\Helpers\StringValidator as SV;
use LLegaz\ZeltyPhpTest\Helpers\UserValidator;

use function count;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
abstract class BaseController
{
    // user inputs
    private array $inputs;
    protected $container;

    protected function __construct(
        ?Container $container
    ) {
        $this->inputs    = [];
        $this->container = $container;
    }

    protected function root(Request $request, Response $response): Response
    {
        $data = [
            'description' => 'Zelty - test de petite application PHP',
            'version'     => '0.1',
            'Author'      => 'Laurent LEGAZ',
        ];

        return JH::sendJsonResponse($request, $response, $data);
    }

    protected function isUrlParameters(Request $request): bool
    {
        return (bool) count($request->getQueryParams());
    }

    protected function getUrlParameter(Request $request, string $param): ?string
    {
        if (isset($request->getQueryParams()[$param])) {
            return SV::validateString($request, $request->getQueryParams()[$param]);
        }

        return null;
    }

    protected function handleInputForTitle(Request $request, bool $required = true): ?string
    {
        return $this->handleInputForString($request, 'title', $required);
    }

    protected function handleInputForContent(Request $request, bool $required = true): ?string
    {
        return $this->handleInputForString($request, 'content', $required);
    }

    protected function handleInputForDate(Request $request, bool $required = true): ?string
    {
        $date = $this->handleInputForString($request, 'publishedOn', $required);

        if ($date !== null) {
            SV::validateDateString($request, $date);
        }

        return $date;
    }

    protected function handleInputForState(Request $request, bool $required = true): ?string
    {
        return $this->handleInputForString($request, 'state', $required);
    }

    protected function handleInputForAuthor(Request $request, bool $required = true): ?User
    {
        $authorUser = null;
        $inputs     = $this->handleUserInput($request);

        if ($required) {
            $author     = IV::validateInputString($request, $inputs, 'author');
            $authorUser = (new UserValidator($this->container))->validateUser($request, $author);
        } else {
            if (isset($inputs['author'])) {
                $author = $inputs['author'];
            } else {
                $author = null;
            }
            $authorUser = (new UserValidator($this->container))->withoutValidateUser($author);
        }

        return $authorUser;
    }

    private function handleInputForString(Request $request, string $arg, bool $required = true): ?string
    {
        $inputs = $this->handleUserInput($request);

        if ($required) {
            $inputString = IV::validateInputString($request, $inputs, $arg);
        } elseif (isset($inputs[$arg])) {
            $inputString = $inputs[$arg];
        } else {
            $inputString = null;
        }

        return $inputString;
    }

    private function handleUserInput(Request $request): array
    {
        if (!count($this->inputs)) {
            $this->inputs = JH::handleJsonRequest($request);
        }

        return $this->inputs;
    }
}
