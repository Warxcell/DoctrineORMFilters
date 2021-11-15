<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

interface HasFilters
{
    public function createQueryBuilderByFilters(
        string $alias,
        iterable $filterBy,
        string $indexBy = null
    ): QueryBuilder;

    public function appendFilter(QueryBuilder $queryBuilder, string $alias, string $filterName, ...$values): bool;

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByFilters(iterable $filterBy): ?object;

    public function findByFilters(iterable $filterBy): array;

    /**
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
