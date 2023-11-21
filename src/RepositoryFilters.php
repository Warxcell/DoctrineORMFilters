<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * @phpstan-import-type Filters from FilterQueryBuilder
 */
trait RepositoryFilters
{
    /**
     * @return Filters
     */
    abstract protected function getFilters(): array;

    /**
     * @param literal-string $alias
     * @param array<string, mixed> $filterBy
     * @param literal-string|null $indexBy
     */
    public function createQueryBuilderByFilters(
        string $alias,
        iterable $filterBy,
        string $indexBy = null
    ): FilterQueryBuilder {
        $queryBuilder = $this->createQueryBuilder($alias, $indexBy);

        $filterQb = new FilterQueryBuilder($queryBuilder, $alias, $this->getFilters());

        foreach ($filterBy as $filter => $value) {
            $filterQb->appendFilter($filter, ...((array)$value));
        }

        return $filterQb;
    }

    public function findOneByFilters(iterable $filterBy): ?object
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param iterable<string, mixed> $filterBy
     */
    public function findByFilters(iterable $filterBy): array
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->queryBuilder->getQuery()->getResult();
    }

    /**
     * @param iterable<string, mixed> $filterBy
     */
    public function getSingleResultByFilters(iterable $filterBy): object
    {
        return $this->createQueryBuilderByFilters('entity', $filterBy)->queryBuilder->getQuery()->getSingleResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param iterable<string, mixed> $filterBy
     */
    public function countByFilters(iterable $filterBy): int
    {
        return (int)$this
            ->createQueryBuilderByFilters('entity', $filterBy)
            ->queryBuilder->select('COUNT(entity)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder(string $alias, string|null $indexBy = null);
}
