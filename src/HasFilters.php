<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * @template V of object
 */
interface HasFilters
{
    public function createQueryBuilderByFilters(
        string $alias,
        iterable $filterBy,
        string $indexBy = null
    ): QueryBuilder;

    public function appendFilter(QueryBuilder $queryBuilder, string $alias, string $filterName, ...$values): bool;

    /**
     * @return V | null
     * @throws NonUniqueResultException
     */
    public function findOneByFilters(iterable $filterBy): ?object;

    /**
     * @return V[]
     */
    public function findByFilters(iterable $filterBy): array;

    /**
     * @return V
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSingleResultByFilters(iterable $filterBy): object;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countByFilters(iterable $filterBy): int;
}
