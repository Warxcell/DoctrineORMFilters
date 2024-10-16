# Do you want the power of reusable queries?

## You are in right place!

## DoctrineORMFilters exposes very simple but powerful API.

### Install library and create your first filter

```php
<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class QuestionRepository extends EntityRepository
{
    use Arxy\DoctrineORMFilters\RepositoryFilters;

    public function getFilters(): array
    {
        return [
            'ids' => static function (FilterQueryBuilder $qb, array $ids): void {
                /* @var (int|string)[] $ids */
                $qb->queryBuilder->andWhere($qb->alias . '.id IN(:ids)')
                    ->setParameter('ids', $ids);
            },
        ];
    }
}
```

### Then you can use all of these methods

```php
// Create Query builder by filter and returns it for further modification, or pagination
$fqb = $repository->createQueryBuilderByFilters('question', [
    'filterName' => 5
]);
//... do other stuff with $qb
$questions = $fqb->queryBuilder->getQuery()->getResult();

// or directly find by filters

$questions = $repository->findByFilters([
    'filterName' => 5
]);

// or counts by filter

$questionsCount = $repository->countByFilters([
    'filterName' => 5
]);

// or finds one result by filter (uses internal ->getOneOrNullResult(); of QueryBuilder)
try {
    $question = $repository->findOneByFilters([
        'filterName' => 5
    ]);
} catch (\Doctrine\ORM\NonUniqueResultException $exception) {
// Exactly one result was expected, 1+ found.
}

// or finds one result by filter (uses internal ->getSingleResult(); of QueryBuilder)
try {
    $question = $repository->getSingleResultByFilters([
        'filterName' => 5
    ]);
} catch (\Doctrine\ORM\NoResultException $exception) {
// Question wasn't found
}

// you can even create your own QueryBuilder and manually append filters to it.

$fqb = $repository->createQueryBuilder('question');
$fqb->appendFilter('ids', [5]);
//... do other stuff with $qb
$results = $qb->getQuery()->getResult();
```

### You can also have filters that calls other filters

```php
<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class QuestionRepository extends EntityRepository
{
    use \Arxy\DoctrineORMFilters\RepositoryFilters;

    public function getFilters(): array
    {
        return [
            'joinX' => static function (FilterQueryBuilder $qb, boolean $value): void {
                $qb->queryBuilder->join(...)
            },
            'x' => static function (FilterQueryBuilder $qb, array $ids): void {
                $fqb->appendFilter('joinX', true);
                
                ....
            },
        ];
    }
}
```

Please note: It's important to use `appendFilter` method, since it will not append same filter twice!

### You can even appendFilter from other repositories. Let's say we have this scenario:

```php
<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class QuestionRepository extends EntityRepository
{
    use \Arxy\DoctrineORMFilters\RepositoryFilters;

    public function getFilters(): array
    {
        return [
             'id' => static function (FilterQueryBuilder $qb, array $id): void {
                  $qb->queryBuilder->andWhere($qb->alias . '.id IN(:ids)')
                    ->setParameter('ids', $ids);;
            },
        ];
    }
}
```

```php
<?php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AnswerRepository extends EntityRepository
{
    use \Arxy\DoctrineORMFilters\RepositoryFilters;

    public function getFilters(): array
    {
        return [
             'questionIds' => static function (FilterQueryBuilder $qb, array $ids): void {
                $qb->queryBuilder->join($qb->alias.'.question', 'question');
                $filterQb = new FilterQueryBuilder(queryBuilder: $qb->queryBuilder, alias: 'question', filters: $this->questionsRepository->getFilters());
                $filterQb->appendFilter('ids', $ids);
            },
        ];
    }
}
```