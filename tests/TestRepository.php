<?php

namespace Arxy\DoctrineORMFilters\Tests;

use Arxy\DoctrineORMFilters\Filters;
use Arxy\DoctrineORMFilters\HasFilters;
use Doctrine\ORM\QueryBuilder;

class TestRepository implements HasFilters
{
    use Filters;

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {

    }

    public function getFilters(): array
    {
        return [];
    }
}
