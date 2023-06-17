<?php
/**
 * User Service Interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Class UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function createPaginationList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param User   $user     User entity
     * @param string $password Password
     */
    public function save(User $user, string $password): void;

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void;

    /**
     * Edit password.
     *
     * @return void
     */
    public function upgradePassword(User $user, string $password): void;
}// end interface
