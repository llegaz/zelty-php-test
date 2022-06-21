<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Controllers;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use LLegaz\ZeltyPhpTest\Helpers\BooleanValidator as BV;
use LLegaz\ZeltyPhpTest\Helpers\InputValidator as IV;
use LLegaz\ZeltyPhpTest\Helpers\JsonHelper as JH;
use LLegaz\ZeltyPhpTest\Repositories\ArticlesRepository;
use Slim\Exception\HttpNotFoundException;
use function filter_var;
use function strlen;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class ArticleController extends BaseController
{
    protected $repository;

    public function __construct(
        Container $container,
        ArticlesRepository $repository
    ) {
        $this->repository = $repository;

        parent::__construct($container);
    }

    /**
     * POST /article/     {"title": "...","content": "...","state": "draft", "publishedOn": "2022/11/15", "author": "..."}
     *                    note: author is user displayName or userId
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response): Response
    {
        $this->setHateoas($request);

        return JH::sendJsonResponse(
            $request,
            $response,
            $this->repository->createArticle(
                $this->handleInputForAuthor($request),
                $this->handleInputForTitle($request),
                $this->handleInputForContent($request),
                $this->handleInputForState($request, false),
                $this->handleInputForDate($request, false),
            )
        );
    }

    public function read(Request $request, Response $response, array $args = []): Response
    {
        // handle URL parameters
        if ($this->isUrlParameters($request)) {
            $this->setRenderHtml($request);
        }
        IV::validateInput($request, $args, 'id');
        $article = $this->repository->getArticle($args['id']);

        if (!$article) {
            throw new HttpNotFoundException($request);
        }

        return JH::sendJsonResponse($request, $response, $article);
    }

    public function update(Request $request, Response $response, array $args = []): Response
    {
        IV::validateInput($request, $args, 'id');
        $article = $this->repository->updateArticle(
            $args['id'],
            $this->handleInputForAuthor($request, false),
            $this->handleInputForTitle($request, false),
            $this->handleInputForContent($request, false),
            $this->handleInputForState($request, false),
            $this->handleInputForDate($request, false)
        );

        if (!$article) {
            throw new HttpNotFoundException($request);
        }

        $this->setHateoas($request);

        return JH::sendJsonResponse($request, $response, $article);
    }

    public function delete(Request $request, Response $response, array $args = []): Response
    {
        IV::validateInput($request, $args, 'id');
        $bln = $this->repository->deleteArticle($args['id']);

        if (!$bln) {
            throw new HttpNotFoundException($request);
        }

        return $response->withStatus(204);
    }

    public function readAll(Request $request, Response $response): Response
    {
        $this->setHateoas($request);
        // handle URL parameters
        if ($this->isUrlParameters($request)) {
            $page = (int) ($this->getUrlParameter($request, 'page'));
            $this->repository->setPage($page > 1 ? $page : 1);
            $perPage = (int) ($this->getUrlParameter($request, 'perPage'));

            if ($perPage < 300 && $perPage > 1) {
                $this->repository->setPerPage($perPage);
            }
            $this->setRenderHtml($request);
            $filters = $this->getUrlParameter($request, 'filters') ??
                    $this->getUrlParameter($request, 'filter');

            if ($filters && strlen($filters)) {
                $this->repository->setFilters($filters);
            }
        }

        return JH::sendJsonResponse(
            $request,
            $response,
            $this->repository->getArticles()
        );
    }

    private function setRenderHtml(Request $request): void
    {
        $renderHtml = $this->getUrlParameter($request, 'renderHtml') ??
                $this->getUrlParameter($request, 'renderHTML');

        if ($renderHtml && strlen($renderHtml)) {
            $this->repository->setRenderHTML(BV::validateBool($request, $renderHtml));
        }
    }

    private function setHateoas(Request $request): void
    {
        $this->repository->setHyperMedia(
            (
                isset($request->getServerParams()['HTTPS'])
                && filter_var(
                    $request->getServerParams()['HTTPS'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                )
            ) ? 'https' : 'http',
            $request->getServerParams()['HTTP_HOST'] ?? ''
        );
    }
}
