<?php

declare(strict_types=1);

namespace Arxy\DoctrineORMFilters\Tests;

use Arxy\DoctrineORMFilters\Filters;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    private QueryBuilder $queryBuilder;
    /** @var Filters */
    private $filters;

    public function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $this->filters = $this->getMockForTrait(Filters::class);
        $this->filters->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $this->filters->method('getFilters')->willReturn(
            [
                'array' => function (QueryBuilder $queryBuilder, string $alias, \stdClass ...$values) {
                    $this->assertEquals([new \stdClass(), new \stdClass()], $values);
                },
                'filterSingleValue' => function (QueryBuilder $queryBuilder, string $alias, int $value) {
                    $this->assertSame(1, $value);
                },
                'filterMultiValue' => function (
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

    public function testCreateQueryBuilderByFilters()
    {
        $this->filters->expects($this->once())->method('createQueryBuilder')->with('alias', 'indexBy');

        $qb = $this->filters->createQueryBuilderByFilters(
            'alias',
            [
                'filterSingleValue' => 1,
                'filterMultiValue' => [2, 'value2'],
                'array' => [new \stdClass(), new \stdClass()],
            ],
            'indexBy'
        );

        $this->assertSame($this->queryBuilder, $qb);
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

    public function testAppendSingleValueFilter()
    {
        $this->filters->appendFilter($this->queryBuilder, 'alias', 'filterSingleValue', 1);
    }

    public function testAppendFilterMultiValueFilter()
    {
        $this->filters->appendFilter($this->queryBuilder, 'alias', 'filterMultiValue', 2, 'value2');
    }
}
