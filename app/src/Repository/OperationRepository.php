<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class OperationRepository.
 *
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Operation>
 *
 * @psalm-supress LessSpecificImplementedReturnType
 */
class OperationRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in configuration files.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('operation', 'category', 'wallet', 'tags', 'author')
            ->join('operation.wallet', 'wallet')
            ->join('operation.category', 'category')
            ->join('operation.tags', 'tags')
            ->join('operation.author', 'author')
            ->orderBy('operation.updatedAt', 'DESC');
    }

    /**
     * Count tasks by category.
     *
     * @param Category $category Category
     *
     * @return int Number of tasks in category
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->getOrCreateQueryBuilder();

        return $qb->select($qb->expr()->countDistinct('task.id'))
            ->where('task.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation): void
    {
        $this->_em->persist($operation);
        $this->_em->flush();
    }

    /**
     * Delete entity.
     *
     * @param Operation $operation Operation entity
     */
    public function delete(Operation $operation): void
    {
        $this->_em->remove($operation);
        $this->_em->flush();
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?? $this->createQueryBuilder('operation');
    }
}
