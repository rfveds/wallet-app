<?php
/**
 * Operation Service Interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface OperationServiceInterface.
 */
interface OperationServiceInterface
{
    /**
     * Create paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function createPaginatedList(int $page, User $author): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation);

    /**
     * Delete entity.
     *
     * @param Operation $operation Operation entity
     */
    public function delete(Operation $operation);

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function findByWallet(Wallet $wallet);

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array<string, mixed> Result
     */
    public function findByUser(User $user): array;

    /**
     * Find by title.
     *
     * @param string $operationTitle
     *
     * @return Operation|null Operation entity
     */
    public function findOneByTitle(string $operationTitle): ?Operation;
}
