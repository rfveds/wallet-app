<?php
/**
 * User Service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Operation service.
     */
    private OperationServiceInterface $operationService;

    /**
     * Wallet service.
     */
    private WalletServiceInterface $walletService;

    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * UserService constructor.
     *
     * @param UserRepository              $userRepository   User repository
     * @param UserPasswordHasherInterface $passwordHasher   Password hasher
     * @param PaginatorInterface          $paginator        Paginator
     * @param CategoryServiceInterface    $categoryService  Category service
     * @param OperationServiceInterface   $operationService Operation service
     * @param WalletServiceInterface      $walletService    Wallet service
     */
    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, PaginatorInterface $paginator, CategoryServiceInterface $categoryService, OperationServiceInterface $operationService, WalletServiceInterface $walletService)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->passwordHasher = $passwordHasher;
        $this->operationService = $operationService;
        $this->walletService = $walletService;
        $this->categoryService = $categoryService;
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
     * @param User   $user     User entity
     * @param string $password Password
     */
    public function save(User $user, string $password): void
    {
        // encode the plain password
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $password
            )
        );

        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user, true);
    }// end save()

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $operations = $this->operationService->findByUser($user);
        foreach ($operations as $operation) {
            $this->operationService->delete($operation);
        }

        $wallets = $this->walletService->findByUser($user);
        foreach ($wallets as $wallet) {
            $this->walletService->delete($wallet);
        }

        $categories = $this->categoryService->findByUser($user);
        foreach ($categories as $category) {
            $this->categoryService->delete($category);
        }

        $this->userRepository->remove($user, true);
    }// end delete()

    /**
     * Edit password.
     *
     * @param User   $user     User entity
     * @param string $password Password
     */
    public function upgradePassword(User $user, string $password): void
    {
        $this->userRepository->upgradePassword($user, $password);
    }

    /**
     * Edit data.
     *
     * @param User $user User entity
     */
    public function editData(User $user): void
    {
        $this->userRepository->save($user, true);
    }// end editData()
}// end class
