<?php
/**
 * User Service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\OperationRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * User repository.
     */
    private UserRepository $userRepository;

    /**
     * Wallet repository.
     */
    private WalletRepository $walletRepository;

    /**
    Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Operation repository.
     */
    private OperationRepository $operationRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository      $userRepository      User repository
     * @param PaginatorInterface  $paginator           Paginator
     * @param OperationRepository $operationRepository Operation repository
     */
    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator, OperationRepository $operationRepository, WalletRepository $walletRepository)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->operationRepository = $operationRepository;
        $this->walletRepository = $walletRepository;

    }// end __construct()

    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function createPaginationList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            UserRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginationList()

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user, true);
    }// end save()

    /**
     * Can be deleted.
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool
    {
        $result = $this->operationRepository->count(['author' => $user]);

        return !($result > 0);
    }// end canBeDeleted()

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        // delete every wallet owned by user
        $wallets = $this->walletRepository->findBy(['user' => $user]);
        foreach ($wallets as $wallet) {
            $this->walletRepository->delete($wallet);
        }

        // delete every operation made by user
        $operations = $this->operationRepository->findBy(['author' => $user]);
        foreach ($operations as $operation) {
            $this->operationRepository->delete($operation);
        }


        $this->userRepository->remove($user, true);
    }// end delete()
}// end class
