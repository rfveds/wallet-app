<?php
/**
 * Wallet repository.
 */

namespace App\Repository;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 *
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Wallet::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->select(
                'partial wallet.{id, title, user, balance}'
            )
            ->orderBy('wallet.title', 'DESC');
    }

    /**
     * Find by id.
     *
     * @throws NonUniqueResultException
     */
    public function findById(int $id): ?Wallet
    {
        return $this->createQueryBuilder('wallet')
            ->select('partial wallet.{id, title, user, balance, type}')
            ->andWhere('wallet.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Query wallets by author.
     *
     * @param User $user Author entity
     *
     * @return QueryBuilder Query builder
     */
    public function findByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('wallet')
            ->select('partial wallet.{id, title, user, balance, type}')
            ->andWhere('wallet.user = :user')
            ->setParameter('user', $user);
    }

    /**
     * Save record.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->_em->persist($wallet);
        $this->_em->flush();
    }

    /**
     * Delete record.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void
    {
        $this->_em->remove($wallet);
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
        return $queryBuilder ?? $this->createQueryBuilder('wallet');
    }

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
        $queryBuilder->andWhere('wallet.user = :author')
            ->setParameter('author', $author);

        return $queryBuilder;
    }
}
