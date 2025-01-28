<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

use function is_string;

/**
 * @ORM\Table(name="articles",indexes={
 *   @ORM\Index(name="idx_articles", columns={"title"})
 * })
 * @ORM\Entity
 */
class Article
{
    /**
     * @ORM\Column(name="article_id", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $articleId;

    /**
     * @ORM\Column(name="title", type="string", length=128, nullable=false)
     */
    private string $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="published_on", type="string", length=32, nullable=true)
     */
    private $publishedOn = 'YYYY-MM-DD HH:MM:SS.SSS';

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="string", length=16, nullable=false)
     */
    private string $state = 'draft';

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var user
     *           Many Articles has One Author
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="articles")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="user_id")
     */
    private User $author;

    public function getArticleId()
    {
        return $this->articleId;
    }

    // true warriors handle things manually
    public function generateArticleId(): self
    {
        $this->articleId = Uuid::uuid6()->toString();

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getPublishedOn(): string
    {
        if ($this->publishedOn instanceof DateTime) {
            return $this->publishedOn->format(DateTime::ATOM);
        }

        return $this->publishedOn;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setContent(?string $content = null): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     *
     * @param string|DateTime $publishedOn
     * @return self
     */
    public function setPublishedOn($publishedOn): self
    {
        if (is_string($publishedOn)) {
            $this->publishedOn = (new DateTime($publishedOn))->format(DateTime::ATOM);
        } elseif ($publishedOn instanceof DateTime) {
            $this->publishedOn = $publishedOn->format(DateTime::ATOM);
        }

        return $this;
    }

    public function setState(string $state = State::DRAFT): self
    {
        if (State::PUB === $state) {
            // no event manager is used for that micro project
            $this->publishedOn = (new DateTime())->format(DateTime::ATOM);
        }
        $this->state = $state;

        return $this;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
