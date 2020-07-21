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
    use FiltersTrait;

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

$question = $repository->findOneByFilters([
    'filterName' => 5
]);

// or finds one result by filter (uses internal ->getSingleResult(); of QueryBuilder)

$question = $repository->getSingleResultByFilters([
    'filterName' => 5
]);

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