<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters;

use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

use function call_user_func_array;
use function count;
use function is_array;
use function is_callable;

trait Filters
{
    private array $filters;

    abstract public function getFilters(): array;

    private function createFilters(): array
    {
        if (!isset($this->filters)) {
            $this->filters = $this->getFilters();
        }

        return $this->filters;
    }

    private function getFilter($name): callable
    {
        $this->createFilters();

        if (!isset($this->filters[$name])) {
            throw new InvalidArgumentException('Filter ' . $name . ' for ' . get_class($this) . ' does not exists');
        }

        return $this->filters[$name];
    }

    /** @return QueryBuilder */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    public function createQueryBuilderByFilters(string $alias, iterable $filterBy, string $indexBy = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($alias, $indexBy);
        $queryBuilder->filters = [];

        foreach ($filterBy as $filter => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }
            $this->appendFilter($queryBuilder, $alias, $filter, ...$values);
        }

        return $queryBuilder;
    }

    public function appendFilter(QueryBuilder $queryBuilder, string $alias, string $filterName, ...$values): bool
    {
        if (isset($queryBuilder->filters[$filterName])) {
            return false;
        }

        if (count($values) === 1 && is_callable($values[0])) {
            call_user_func_array($values[0], [$queryBuilder, $alias]);
        } else {
            $filter = $this->getFilter($filterName);
            call_user_func_array($filter, [$queryBuilder, $alias, ...$values]);
        }

        return $queryBuilder->filters[$filterName] = true;
    }

    public function findOneByFilters(iterable $filterBy): ?object
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getOneOrNullResult();
    }

    public function findByFilters(iterable $filterBy): array
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getResult();
    }

    public function getSingleResultByFilters(iterable $filterBy): object
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->getQuery()->getSingleResult();
    }

    public function countByFilters(iterable $filterBy): int
    {
        return (int)$this
            ->createQueryBuilderByFilters('entity', $filterBy)
            ->select('COUNT(entity)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
