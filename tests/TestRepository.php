<?php

namespace Arxy\DoctrineORMFilters\Tests;

use Arxy\DoctrineORMFilters\Filters;
use Arxy\DoctrineORMFilters\HasFilters;

class TestRepository implements HasFilters
{
    use Filters;

    public function createQueryBuilder($alias, $indexBy = null)
    {

    }

    public function getFilters(): array
    {
        return [];
    }
}