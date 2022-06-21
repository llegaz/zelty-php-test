<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="users",indexes={
 *   @ORM\Index(name="idx_users", columns={"display_name"})
 * })
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Column(name="user_id", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $userId;

    public const DEFAULT_DN = 'anonymous';

    /**
     * @ORM\Column(name="display_name", type="string", length=32, nullable=false)
     */
    private string $displayName = self::DEFAULT_DN;

    /**
     * @ORM\Column(name="login", type="string", length=32, nullable=false)
     */
    private string $login;

    /**
     * @ORM\Column(name="pwd_hash", type="text", nullable=false)
     */
    private string $passwordHash;

    /**
     * @ORM\Column(name="pwd_salt", type="text", nullable=false)
     */
    private string $passwordSalt;

    /**
     * @var arrayCollection
     *                      One author has Many Articles
     *
     * @ORM\OneToMany(targetEntity="Article", mappedBy="author")
     */
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getUserId()
    {
        return $this->userId;
    }

    // true warriors handle things manually
    public function generateUserId(): self
    {
        $this->userId = Uuid::uuid6()->toString();

        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getPasswordSalt(): string
    {
        return $this->passwordSalt;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function setPasswordSalt(string $passwordSalt): self
    {
        $this->passwordSalt = $passwordSalt;

        return $this;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function setArticles(Collection $articles): self
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Add article.
     *
     * @param \LLegaz\ZeltyPhpTest\Entities\Article $article
     *
     * @return User
     */
    public function addArticle(Article $article): self
    {
        $this->articles[] = $article;

        return $this;
    }

    /**
     * Remove article.
     *
     * @param \LLegaz\ZeltyPhpTest\Entities\Article $article
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeArticle(Article $article): self
    {
        return $this->articles->removeElement($article);
    }
}
