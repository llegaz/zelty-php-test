<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Repositories;

use Doctrine\ORM\EntityManager;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
abstract class BaseRepository
{
    /**
     * @var \LLegaz\ZeltyPhpTest\Entities\EntitiesManager
     */
    protected $em;

    private const PER_PAGE_DEFAULT                = 10;
    private int $perPage                          = self::PER_PAGE_DEFAULT;
    private int $page                             = 1;
    private string $filters                       = '';
    private $renderHTML                           = false;
    private string $hyperMedia                    = '';
    private string $hyperMediaResource            = '';
    private string $hyperMediaResourcesCollection = '';

    protected function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getMaxPerPageItems(): int
    {
        return $this->perPage;
    }

    public function setMaxPerPageItems(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getPerPage(): int
    {
        return $this->getMaxPerPageItems();
    }

    public function setPerPage(int $perPage): self
    {
        return $this->setMaxPerPageItems($perPage);
    }

    public function getFilters(): string
    {
        return $this->filters;
    }

    public function setFilters(string $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getRenderHTML(): bool
    {
        return $this->renderHTML;
    }

    public function setRenderHTML(bool $renderHTML): self
    {
        $this->renderHTML = $renderHTML;

        return $this;
    }

    public function getHyperMedia(): string
    {
        return $this->hyperMedia;
    }

    public function getHyperMediaSingleResource(): string
    {
        return $this->hyperMediaResource;
    }

    public function getHyperMediaResourcesCollection(): string
    {
        return $this->hyperMediaResourcesCollection;
    }

    public function setHyperMedia(string $http, string $host, string $resource): self
    {
        $this->hyperMedia                    = $http . '://' . $host;
        $this->hyperMediaResource            = $this->hyperMedia . '/' . $resource . '/';
        $this->hyperMediaResourcesCollection = $this->hyperMedia . '/' . $resource . 's';

        return $this;
    }
}
