<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers\Queries;

use function stristr;

/**
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class QueryFilter
{
    private static array $acceptedKeys = [
        'name'    => 'u.displayName',
        'author'  => 'u.displayName',
        'title'   => 'a.title',
        'content' => 'a.content',
    ];
    private string $key      = '';
    private string $value    = '';
    private string $operator = 'LIKE';

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setKey(string $key): self
    {
        foreach (self::$acceptedKeys as $acceptedKey => $dqlKey) {
            if (stristr($key, $acceptedKey)) {
                $this->key = $dqlKey;

                return $this;
            }
        }
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setOperator(string $operator): void
    {
        throw new \LLegaz\ZeltyPhpTest\Exceptions\NotSupportedException();
    }
}
