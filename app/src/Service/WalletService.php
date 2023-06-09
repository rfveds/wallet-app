<?php
/**
 * Wallet Service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class WalletService.
 */
class WalletService implements WalletServiceInterface
{
    /**
     * Wallet repository.
     */
    private WalletRepository $walletRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * WalletService constructor.
     *
     * @param WalletRepository   $walletRepository Wallet repository
     * @param PaginatorInterface $paginator        Paginator
     */
    public function __construct(WalletRepository $walletRepository, PaginatorInterface $paginator)
    {
        $this->walletRepository = $walletRepository;
        $this->paginator = $paginator;
    }// end __construct()

    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface Paginated list
     */
    public function createPaginatedList(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->walletRepository->queryByAuthor($author),
            $page,
            WalletRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginatedList()

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->walletRepository->save($wallet);
    }// end save()

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void
    {
        $this->walletRepository->delete($wallet);
    }// end delete()

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array<string, mixed> Result
     */
    public function findByUser(User $user): array
    {
        return $this->walletRepository->findByUser($user)->getQuery()->getResult();
    }// end findByUser()

    /**
     * Find one by id.
     *
     * @param int $walletId Wallet id
     *
     * @return Wallet|null Wallet entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $walletId): ?Wallet
    {
        return $this->walletRepository->findOneById($walletId);
    }// end findOneById()
}// end class
