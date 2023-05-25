<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Service;

use App\Entity\Operation;
use App\Entity\User;
use App\Repository\OperationRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class OperationService.
 */
class OperationService implements OperationServiceInterface
{
    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Operation repository.
     */
    private OperationRepository $operationRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * OperationService constructor.
     *
     * @param OperationRepository      $operationRepository Operation repository
     * @param PaginatorInterface       $paginator           Paginator
     * @param CategoryServiceInterface $categoryService     Category service
     * @param TagServiceInterface      $tagService          Tag service
     */
    public function __construct(OperationRepository $operationRepository, PaginatorInterface $paginator, CategoryServiceInterface $categoryService, TagServiceInterface $tagService)
    {
        $this->operationRepository = $operationRepository;
        $this->paginator = $paginator;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }// end __construct()

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param User               $author  Author
     * @param array<string, int> $filters Filters
     *
     * @return PaginationInterface Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function createPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->operationRepository->queryByAuthor($author, $filters),
            $page,
            OperationRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginatedList()

    /**
     * Save entity.
     *
     * @param Operation $operation Operation entity
     */
    public function save(Operation $operation): void
    {
        $this->operationRepository->save($operation);
    }// end save()

    /**
     * Delete entity.
     *
     * @param Operation $operation Operation entity
     */
    public function delete(Operation $operation): void
    {
        $this->operationRepository->delete($operation);
    }// end delete()

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }// end prepareFilters()
}// end class
