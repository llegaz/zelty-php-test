<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Helpers\Queries;

/**
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 */
class QueryFilterOR
{
    private QueryFilter $leftMember;
    private QueryFilter $rightMember;

    public function __construct(QueryFilter $leftMember, QueryFilter $rightMember)
    {
        $this->leftMember  = $leftMember;
        $this->rightMember = $rightMember;
    }

    public function getLeftOperand(): QueryFilter
    {
        return $this->leftMember;
    }

    public function getRightOperand(): QueryFilter
    {
        return $this->rightMember;
    }
}
