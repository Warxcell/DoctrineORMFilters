<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters\Tests;

use Arxy\DoctrineORMFilters\Filters;
use Doctrine\ORM\QueryBuilder;

class FiltersTest extends \PHPUnit\Framework\TestCase
{
    private QueryBuilder $queryBuilder;
    private $filters;

    public function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $this->filters = $this->getMockForTrait(Filters::class);
        $this->filters->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $this->filters->method('getFilters')->willReturn(
            [
                'filterSingleValue' => function (QueryBuilder $queryBuilder, string $alias, int $value) {
                    $this->assertSame(1, $value);
                },
                'filterSingleMultiValue' => function (
                    QueryBuilder $queryBuilder,
                    string $alias,
                    int $value,
                    string $value1
                ) {
                    $this->assertSame(2, $value);
                    $this->assertSame('value2', $value1);
                },
            ]
        );
    }

    public function testCallable()
    {
        $this->filters->appendFilter(
            $this->queryBuilder,
            'alias',
            'callable',
            function (QueryBuilder $queryBuilder, string $alias) {
                $this->expectNotToPerformAssertions();
            }
        );
    }

    public function testSingleValueFilter()
    {
        $this->filters->appendFilter($this->queryBuilder, 'alias', 'filterSingleValue', 1);
    }

    public function testMultiValueFilter()
    {
        $this->filters->appendFilter($this->queryBuilder, 'alias', 'filterSingleMultiValue', 2, 'value2');
    }
}