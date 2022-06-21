<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Repositories;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use LLegaz\ZeltyPhpTest\Entities\Article;
use LLegaz\ZeltyPhpTest\Entities\State;
use LLegaz\ZeltyPhpTest\Entities\User;
use LLegaz\ZeltyPhpTest\Exceptions\NotSupportedException;
use LLegaz\ZeltyPhpTest\Helpers\Queries\QueryFilter;
use LLegaz\ZeltyPhpTest\Helpers\Queries\QueryFilterAND;
use LLegaz\ZeltyPhpTest\Helpers\Queries\QueryFilterOR;
use LLegaz\ZeltyPhpTest\Utils as ZU;
use function count;
use function explode;
use function is_int;
use function is_string;
use function strlen;
use function strtotime;
use function time;
use function trim;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class ArticlesRepository extends BaseRepository
{
    private QueryBuilder $qb;

    public function __construct(EntityManager $em)
    {
        parent::__construct($em);

        $this->qb = new QueryBuilder($em);
    }

    public function createArticle(User $author, string $title, string $content, ?string $state, ?string $publishedOn): array
    {
        $article = new Article();
        $article->generateArticleId()
            ->setAuthor($author)
            ->setTitle($title)
            ->setContent($content)
        ;

        if ($state !== null && $state === State::PUB) {
            $article->setState($state)
                ->setPublishedOn(new DateTime())
            ;
        } else {
            $article->setState(State::DRAFT);

            if ($publishedOn && (strtotime($publishedOn) > time())) {
                // set only on future date here
                $article->setPublishedOn($publishedOn);
            }
        }
        $this->em->persist($article);
        $this->em->flush();

        return $this->formatArticleObjectArrayRendering($article);
    }

    public function updateArticle(
        string $id,
        ?User $author,
        ?string $title,
        ?string $content,
        ?string $state,
        ?string $publishedOn
    ): array {
        $article = $this->retrieveArticleByID($id);

        if (!($article instanceof Article)) {
            return [];
        }

        if ($author instanceof User) {
            $article->setAuthor($author);
        }

        if (is_string($title)) {
            $article->setTitle($title);
        }

        if (is_string($content)) {
            $article->setContent($content);
        }

        if (is_string($state)) {
            $article->setState($state);
        }

        if (is_string($publishedOn)) {
            $article->setPublishedOn($publishedOn);
        }
        $this->em->persist($article);
        $this->em->flush();

        return $this->formatArticleObjectArrayRendering($article);
    }

    public function deleteArticle(string $id): bool
    {
        $result = $this->qb->delete()
            ->from(Article::class, 'a')
            ->where('a.articleId = :id')
            ->setParameter('id', $id, Types::STRING)
            ->getQuery()
            ->execute()
        ;

        if (!is_int($result) || $result > 1) {
            throw new LogicException('Article Repository: should not happen');
        }

        return (bool) $result;
    }

    public function getArticle(string $id): array
    {
        $this->qb->select(['a', 'u.displayName'])
            ->from(Article::class, 'a')
            ->where('a.articleId = :id')
            ->leftJoin('a.author', 'u')
            ->setParameter('id', $id, Types::STRING)
            ->setMaxResults(1)
        ;
        $results = $this->qb->getQuery()->getArrayResult();

        if (!count($results)) {
            return [];
        }
        $article = $results[0];

        return $this->formatArrayRendering($article);
    }

    /**
     * As stated by JJ. Geewax, offset / limit pattern is an anti-pattern for pagination
     * because it couples tightly pagination requests with underlying implementation
     * (this could become problematic in case the API evolves to another implementation,
     * e.g NoSQL, making difficult to keep on with this pattern just for sake of backward
     * compatibility and user friendliness).
     *
     * It could be largely improved but will be ideal for this exercise ;)
     * .
     */
    public function getArticles(): array
    {
        $articles = [];
        $this->qb->select(['a', 'u.displayName'])
            ->from(Article::class, 'a')
            ->orderBy('a.articleId', 'ASC')
            ->leftJoin('a.author', 'u')
        ;

        if (strlen($this->getFilters())) {
            $this->handleFilters();
        }
        $query = $this->qb
            ->getQuery()
            ->setHydrationMode(Query::HYDRATE_ARRAY)
        ;

        // paginate
        $query->setFirstResult($this->getMaxPerPageItems() * ($this->getPage() - 1))
            ->setMaxResults($this->getMaxPerPageItems())
        ;
        $paginator  = new Paginator($query, true);
        $totalItems = count($paginator);

        foreach ($paginator as $article) {
            $articles['articles'][] = $this->formatArrayRendering($article);
        }

        // misc + hypermedia (HATEOAS)
        $articles['perPage'] = $this->getMaxPerPageItems();

        if ($totalItems > $this->getPage() * $this->getPerPage()) {
            /**
             * @todo refacto this
             */
            $articles['nextPage'] = $this->getHyperMediaResourcesCollection() .
                    '?page=' . ($this->getPage() + 1) .
                    '&perPage=' . $this->getPerPage() .
                    ($this->getRenderHTML() ? '&renderHTML=yes' : '') .
                    (strlen($this->getFilters()) ? '&filters=' . ZU::sanitizeUrl($this->getFilters()) : '')
            ;
        }

        if ($this->getPage() > 1) {
            /**
             * @todo refacto this
             */
            $articles['previousPage'] = $this->getHyperMediaResourcesCollection() .
                    '?page=' . ($this->getPage() - 1) .
                    '&perPage=' . $this->getPerPage() .
                    ($this->getRenderHTML() ? '&renderHTML=yes' : '') .
                    (strlen($this->getFilters()) ? '&filters=' . ZU::sanitizeUrl($this->getFilters()) : '')
            ;
        }

        $articles['totalItems'] = $totalItems;

        return $articles;
    }

    /**
     * We handle only very simple filters for now
     *
     * @throws \LLegaz\ZeltyPhpTest\Exceptions\NotSupportedException
     */
    private function handleFilters(): void
    {
        $and = explode('AND', $this->getFilters());
        $or  = explode('OR', $this->getFilters());

        if (count($and) > 2 || count($or) > 2) {
            throw new NotSupportedException();
        }

        if (count($and) > 1) {
            $queryFilter = new QueryFilterAND(
                $this->getSupportedFilterQuery($and[0]),
                $this->getSupportedFilterQuery($and[1])
            );
        } elseif (count($or) > 1) {
            $queryFilter = new QueryFilterOR(
                $this->getSupportedFilterQuery($or[0]),
                $this->getSupportedFilterQuery($or[1])
            );
        } else {
            // simplest case
            $queryFilter = $this->getSupportedFilterQuery($this->getFilters());
        }

        // handle filters here
        if ($queryFilter instanceof QueryFilter) {
            $this->addWhere($queryFilter);
        } elseif ($queryFilter instanceof QueryFilterAND) {
            $this->addAndWhere($queryFilter);
        } elseif ($queryFilter instanceof QueryFilterOR) {
            $this->addOrWhere($queryFilter);
        }
    }

    private function addWhere(QueryFilter $queryFilter): void
    {
        $this->qb->where(
            $this->qb->expr()->like($queryFilter->getKey(), ':like')
        )
            ->setParameter('like', '%' . $queryFilter->getValue() . '%', Types::STRING)
            ;
    }

    private function addAndWhere(QueryFilterAND $queryFilter): void
    {
        $this->qb->where(
            $this->qb->expr()->like($queryFilter->getLeftOperand()->getKey(), ':like')
        )
            ->setParameter('like', '%' . $queryFilter->getLeftOperand()->getValue() . '%', Types::STRING)
            ;
        $this->qb->andWhere(
            $this->qb->expr()->like($queryFilter->getRightOperand()->getKey(), ':like2')
        )
            ->setParameter('like2', '%' . $queryFilter->getRightOperand()->getValue() . '%', Types::STRING)
            ;
    }

    private function addOrWhere(QueryFilterOR $queryFilter): void
    {
        $this->qb->where(
            $this->qb->expr()->like($queryFilter->getLeftOperand()->getKey(), ':like')
        )
            ->setParameter('like', '%' . $queryFilter->getLeftOperand()->getValue() . '%', Types::STRING)
            ;
        $this->qb->orWhere(
            $this->qb->expr()->like($queryFilter->getRightOperand()->getKey(), ':like2')
        )
            ->setParameter('like2', '%' . $queryFilter->getRightOperand()->getValue() . '%', Types::STRING)
            ;
    }

    /**
     * only LIKE operator is supported
     *
     * @param string $condition
     * @return QueryFilter
     * @throws NotSupportedException
     */
    private function getSupportedFilterQuery(string $condition): QueryFilter
    {
        $conditions = explode('LIKE', $condition);

        if (count($conditions) !== 2) {
            throw new NotSupportedException();
        }

        return (new QueryFilter())
            ->setKey($conditions[0])
            ->setValue(trim($conditions[1]))
        ;
    }

    private function retrieveArticleByID(string $id): ?Article
    {
        return $this->em->getRepository(Article::class)->findOneBy(['articleId' => $id]);
    }

    private function formatArticleObjectArrayRendering(Article $article): array
    {
        $arrArticle                = [];
        $arrArticle['articleId']   = $article->getArticleId();
        $arrArticle['title']       = $article->getTitle();
        $arrArticle['author']      = $article->getAuthor()->getDisplayName();
        $arrArticle['authorId']    = $article->getAuthor()->getUserId();
        $arrArticle['content']     = $article->getContent();
        $arrArticle['state']       = $article->getState();
        $arrArticle['publishedOn'] = $article->getPublishedOn();
        $arrArticle['articleUrl']  = $this->getHyperMediaSingleResource() . $article->getArticleId();

        return $arrArticle;
    }

    private function formatArrayRendering(array $result): array
    {
        if (!count($result)) {
            throw new LogicException('Article Repository: should not happen');
        }

        if ($this->getRenderHTML()) {
            $result[0]['content'] = ZU::sanitizeForHtml5Client($result[0]['content']);
        }
        $result[0]['author'] = $result['displayName'] ?? User::DEFAULT_DN;

        if (strlen($this->getHyperMediaSingleResource())) {
            $result[0]['articleUrl'] = $this->getHyperMediaSingleResource() . $result[0]['articleId'];
        }

        return $result[0];
    }

    public function setHyperMedia(string $http, string $host, string $resource = 'article'): self
    {
        parent::setHyperMedia($http, $host, $resource);

        return $this;
    }
}
