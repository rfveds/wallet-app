<?php
/**
 * Category Service Interface.
 */

namespace App\Service;

use App\Entity\Category;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Create paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function createPaginatedList(int $page): PaginationInterface;

    /**
     * Find entity by ID.
     *
     * @param int $id Entity ID
     *
     * @return Category|null Category entity
     */
    public function findOneById(int $id): ?Category;

    /**
     * Find entity by title.
     *
     * @param string $title Entity title
     *
     * @return Category|null Category entity
     */
    public function findOneByTitle(string $title): ?Category;

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;

    /**
     * Delete entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void;

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool;
}// end interface
