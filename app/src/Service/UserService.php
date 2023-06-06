<?php
/**
 * User Service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\OperationRepository;
use App\Repository\UserRepository;
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
    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator, OperationRepository $operationRepository)
    {
        $this->userRepository = $userRepository;
        $this->paginator = $paginator;
        $this->operationRepository = $operationRepository;
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

    public function delete(User $user): void
    {
        // TODO: Implement delete() method.
    }// end delete()
}// end class
