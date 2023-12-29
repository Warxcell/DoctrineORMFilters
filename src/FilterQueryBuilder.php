<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters;

use Doctrine\ORM\QueryBuilder;

use function call_user_func_array;
use function ucfirst;

/**
 * @phpstan-type Filter callable(self, mixed): void
 * @phpstan-type Filters array<string, Filter>
 */
final class FilterQueryBuilder
{
    /**
     * @var array<string, bool>
     */
    private array $appliedFilters = [];

    public function __construct(
        public readonly QueryBuilder $queryBuilder,
        /**
         * @var literal-string
         */
        public readonly string $alias,
        /**
         * @var Filters
         */
        private readonly array $filters
    ) {
    }

    public function makeAlias(string $alias): string
    {
        return $this->alias.ucfirst($alias);
    }

    public function appendFilter(string $filterName, mixed $value): bool
    {
        if ($this->appliedFilters[$filterName] ?? false) {
            return false;
        }

        call_user_func_array($this->filters[$filterName], [$this, $value]);

        return $this->appliedFilters[$filterName] = true;
    }
}
