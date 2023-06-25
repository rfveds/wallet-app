<?php
/**
 * Operation Service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
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
     * Wallet service.
     */
    private WalletServiceInterface $walletService;

    /**
     * OperationService constructor.
     *
     * @param OperationRepository      $operationRepository Operation repository
     * @param PaginatorInterface       $paginator           Paginator
     * @param CategoryServiceInterface $categoryService     Category service
     * @param TagServiceInterface      $tagService          Tag service
     * @param WalletServiceInterface   $walletService       Wallet service
     */
    public function __construct(OperationRepository $operationRepository, PaginatorInterface $paginator, WalletServiceInterface $walletService, CategoryServiceInterface $categoryService, TagServiceInterface $tagService)
    {
        $this->operationRepository = $operationRepository;
        $this->paginator = $paginator;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
        $this->walletService = $walletService;
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
        $filters = $this->prepareFilters($filters, $author);

        if (isset($filters['empty'])) {
            // pagination not found
            return $this->paginator->paginate(
                // return empty array
                [],
                $page,
                OperationRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->operationRepository->queryByAuthor($author, $filters),
            $page,
            OperationRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginatedList()

    /**
     * Create filtered list.
     *
     * @param array $filters Filters
     *
     * @throws NonUniqueResultException
     */
    public function createList(User $user, array $filters = []): array
    {
        $filters = $this->prepareFilters($filters, $user);

        return $this->operationRepository->queryByAuthor($user, $filters)->getQuery()->getResult();
    }// end createList()

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

    // end delete()

    /**
     * Find by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return array Result
     */
    public function findByWallet(Wallet $wallet): array
    {
        return $this->operationRepository->queryByWallet($wallet)->getQuery()->getResult();
    }// end findByWallet()

    /**
     * Find by user.
     *
     * @param User $user User entity
     *
     * @return array Result
     */
    public function findByUser(User $user): array
    {
        return $this->operationRepository->queryByAuthor($user)->getQuery()->getResult();
    }// end findByUser()

    /**
     * Find by title.
     *
     * @param string $operationTitle Operation title
     *
     * @return Operation|null Operation entity
     */
    public function findOneByTitle(string $operationTitle): ?Operation
    {
        return $this->operationRepository->findOneBy(['title' => $operationTitle]);
    }// end findOneByTitle()

    /**
     * Prepare filters for the operation list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(array $filters, User $author): array
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

        if (!empty($filters['wallet_id'])) {
            $wallet = $this->walletService->findOneById($filters['wallet_id']);
            if (null !== $wallet) {
                $resultFilters['wallet'] = $wallet;
            }
        }

        if (!empty($filters['operation_title'])) {
            $operation = $this->findOneByTitle($filters['operation_title']);
            if (null !== $operation && $operation->getAuthor() === $author) {
                $resultFilters['operation'] = $operation;
            } else {
                $resultFilters['empty'] = true;
            }
        }

        if (!empty($filters['operation_date_from'])) {
            $resultFilters['operation_date_from'] = $filters['operation_date_from'];
        }

        if (!empty($filters['operation_date_to'])) {
            $resultFilters['operation_date_to'] = $filters['operation_date_to'];
        }

        return $resultFilters;
    }// end prepareFilters()
}// end class
