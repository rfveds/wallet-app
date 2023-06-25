<?php
/**
 * Operation repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param array $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(array $filters): QueryBuilder
    {
        $queryBuilder = $this->getOrCreateQueryBuilder()
            ->select(
                'partial operation.{id, amount, title, createdAt, updatedAt, currentBalance}',
                'partial category.{id, title}',
                'partial wallet.{id, title}',
                'partial tags.{id, title}',
                'partial author.{id, email}'
            )
            ->join('operation.wallet', 'wallet')
            ->join('operation.category', 'category')
            ->leftJoin('operation.tags', 'tags')
            ->join('operation.author', 'author')
            ->orderBy('operation.createdAt', 'ASC');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Query operation by author.
     *
     * @param UserInterface         $user    User entity
     * @param array<string, object> $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(UserInterface $user, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder
            ->andWhere('operation.author = :author')
            ->setParameter('author', $user);

        return $queryBuilder;
    }

    /**
     * Query operation by wallet.
     *
     * @param Wallet                $wallet  Wallet entity
     * @param array<string, object> $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryByWallet(Wallet $wallet, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->queryAll($filters);

        $queryBuilder
            ->andWhere('operation.wallet = :wallet')
            ->setParameter('wallet', $wallet);

        return $queryBuilder;
    }

    /**
     * Count operation by category.
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

        return $qb->select($qb->expr()->countDistinct('operation.id'))
            ->where('operation.category = :category')
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

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder          $queryBuilder Query builder
     * @param array<string, object> $filters      Filters array
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, array $filters = []): QueryBuilder
    {
        if (isset($filters['category']) && $filters['category'] instanceof Category) {
            $queryBuilder
                ->andWhere('category = :category')
                ->setParameter('category', $filters['category']);
        }

        if (isset($filters['tag']) && $filters['tag'] instanceof Tag) {
            $queryBuilder
                ->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters['tag']);
        }

        if (isset($filters['operation']) && $filters['operation'] instanceof Operation) {
            $queryBuilder
                ->andWhere('operation = :operation')
                ->setParameter('operation', $filters['operation']);
        }

        if (isset($filters['wallet']) && $filters['wallet'] instanceof Wallet) {
            $queryBuilder
                ->andWhere('wallet = :wallet')
                ->setParameter('wallet', $filters['wallet']);
        }

        if (isset($filters['operation_date_from'])) {
            $queryBuilder
                ->andWhere('operation.createdAt >= :operation_date_from')
                ->setParameter('operation_date_from', $filters['operation_date_from']);
        }

        if (isset($filters['operation_date_to'])) {
            $queryBuilder
                ->andWhere('operation.createdAt <= :operation_date_to')
                ->setParameter('operation_date_to', $filters['operation_date_to']);
        }

        return $queryBuilder;
    }
}
