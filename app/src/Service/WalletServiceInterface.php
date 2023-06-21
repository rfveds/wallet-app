<?php
/**
 * Wallet Service Interface.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class WalletServiceInterface.
 */
interface WalletServiceInterface
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
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet);

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet);

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array<string, mixed> Result
     */
    public function findByUser(User $user): array;

    /**
     * Find one by id.
     *
     * @param int $wallet_id Wallet id
     *
     * @return Wallet|null Wallet entity
     */
    public function findOneById(int $wallet_id): ?Wallet;
}
