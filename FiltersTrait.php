<?php

declare(strict_types=1);

namespace DoctrineORMFilters;

use Doctrine\ORM\QueryBuilder;

trait FiltersTrait
{
    private array $appendedFilters = [];
    private array $filters;

    abstract public function getFilters(): array;

    private function createFilters(): array
    {
        if (!isset($this->filters)) {
            $this->filters = $this->getFilters();
        }

        return $this->filters;
    }

    private function getFilter($name)
    {
        $this->createFilters();

        if (!isset($this->filters[$name])) {
            throw new \InvalidArgumentException('Filter '.$name.' for '.get_class($this).' does not exists');
        }

        return $this->filters[$name];
    }

    abstract public function createQueryBuilder($alias, $indexBy = null);

    public function createQueryBuilderByFilters($alias, $filterBy): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($alias);

        foreach ($filterBy as $filter => $value) {
            $this->appendFilter($queryBuilder, $alias, $filter, $value);
        }

        return $queryBuilder;
    }

    public function appendFilter(QueryBuilder $queryBuilder, string $alias, string $filterName, $value)
    {
        $queryBuilderId = spl_object_id($queryBuilder);
        if (isset($this->appendedFilters[$queryBuilderId][$filterName])) {
            return false;
        }

        if (is_callable($value)) {
            call_user_func_array($value, [$queryBuilder, $alias]);
        } else {
            $filter = $this->getFilter($filterName);
            call_user_func_array($filter, [$queryBuilder, $alias, $value]);
        }

        return $this->appendedFilters[$queryBuilderId][$filterName] = true;
    }

    public function findOneByFilters(array $filterBy)
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getOneOrNullResult();
    }

    public function findByFilters(array $filterBy)
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getResult();
    }

    public function getSingleResultByFilters($filterBy)
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getSingleResult();
    }

    public function countByFilters($filterBy)
    {
        return $this
            ->createQueryBuilderByFilters('entity', $filterBy)
            ->select('COUNT(entity)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}