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
    use \Arxy\DoctrineORMFilters\Filters;

    public function getFilters(): array
    {
        return [
            'filterName' => [$this, 'filterByXX']
        ];
    }

    public function filterByXX(QueryBuilder $queryBuilder, string $alias, int $id)
    {
        $queryBuilder->andWhere($alias.'.id = :id');
        $queryBuilder->setParameter('id', $id);
    }
}
```

### Then you can use all of these methods

```php
// Create Query builder by filter and returns it for further modification, or pagination
$qb = $repository->createQueryBuilderByFilters('question', [
    'filterName' => 5
]);
//... do other stuff with $qb
$questions = $qb->getQuery()->getResult();

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

$qb = $repository->createQueryBuilder('question');
$repository->appendFilter($qb, 'question', 'filterName', 5);
//... do other stuff with $qb
$questions = $qb->getQuery()->getResult();
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
    use FiltersTrait;

    public function getFilters(): array
    {
        return [
            'filter1' => [$this, 'filterByOne'],
            'filter2' => [$this, 'filterByTwo']
        ];
    }

    public function filterByOne(QueryBuilder $queryBuilder, string $alias)
    {
        $queryBuilder->join($alias.'.answer', 'answer')->addSelect('answer');
    }

    public function filterByTwo(QueryBuilder $queryBuilder, string $alias, int $id)
    {
        $this->appendFilter($queryBuilder, $alias, 'filter1', true);

        $queryBuilder->andWhere($alias.'.id = :id');
        $queryBuilder->setParameter('id', $id);
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
    use FiltersTrait;

    public function getFilters(): array
    {
        return [
            'id' => [$this, 'filterById'],
        ];
    }

    public function filterById(QueryBuilder $queryBuilder, string $alias, int $id)
    {
        $queryBuilder->andWhere($alias.'.id = :id');
        $queryBuilder->setParameter('id', $id);
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
    use FiltersTrait;

    public function getFilters(): array
    {
        return [
            'joinQuestions' => [$this, 'joinQuestions'],
            'questionId' => [$this, 'filterByQuestionId'],
        ];
    }

    public function joinQuestions(QueryBuilder $queryBuilder, string $alias)
    {
        $queryBuilder->join($alias.'.question', 'question')->addSelect('question');
    }

    public function filterByQuestionId(QueryBuilder $queryBuilder, string $alias, int $id)
    {
        $this->appendFilter($queryBuilder, $alias, 'joinQuestions', true);
        $questionsRepository = $this->getEntityManager()->getRepository(Question::class);
        $questionsRepository->appendFilter($queryBuilder, 'question', 'id', $id);
    }
}
```

### You can also pass multiple arguments:

```php
$qb = $repository->createQueryBuilderByFilters('question', [
    'filterName' => ['value1', 'value2']
]);
```

or when appending filter

```php
  $this->appendFilter($queryBuilder, $alias, 'filterMultiValue', 'value1', 'value2');


  $filters = [
  'filterSingleMultiValue' => function (
            \Doctrine\ORM\QueryBuilder $queryBuilder,
            string $alias,
            string $value1,
            string $value2
        ) {
          
        },
];
```

### And finally you can pass Callback as Filter value.

```php
$repository->findByFilters([
    'filterName' => function(QueryBuilder $qb, string $alias) use ($param) {
        $qb->andWhere($alias.'....');
        $qb->setParameter('param', $param);
        // do other stuffs 
    }
]);
```