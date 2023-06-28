<?php
/**
 * Report repository.
 */

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 *
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
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
     * ReportRepository constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select('report')
            ->orderBy('report.title', 'DESC');
    }// end queryAll()

    /**
     * Query wallets by author.
     *
     * @param User $author Author entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $author): QueryBuilder
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->andWhere('report.author = :author')->setParameter('author', $author);

        return $queryBuilder;
    }// end queryByAuthor()

    /**
     * Save record.
     *
     * @param Report $entity Report entity
     * @param bool   $flush  Flush entity manager?
     */
    public function save(Report $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove record.
     *
     * @param Report $entity Report entity
     * @param bool   $flush  Flush entity manager?
     */
    public function remove(Report $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return array Result
     */
    public function findByWallet(Wallet $wallet): array
    {
        return $this->getOrCreateQueryBuilder()
            ->select('report')
            ->andWhere('report.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('report.title', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array Result
     */
    public function findByUser(User $user): array
    {
        return $this->getOrCreateQueryBuilder()
            ->select('report')
            ->andWhere('report.author = :user')
            ->setParameter('user', $user)
            ->orderBy('report.title', 'DESC')
            ->getQuery()
            ->getResult();
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
        return $queryBuilder ?? $this->createQueryBuilder('report');
    }// end getOrCreateQueryBuilder()
}
