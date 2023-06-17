<?php
/**
 * Category Service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\OperationRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Interface CategoryService.
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Category>
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Category repository.
     */
    private CategoryRepository $categoryRepository;

    /**
     * Operation repository.
     */
    private OperationRepository $operationRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * CategoryService constructor.
     *
     * @param CategoryRepository  $categoryRepository  Category repository
     * @param OperationRepository $operationRepository Operation repository
     * @param PaginatorInterface  $paginator           Paginator
     */
    public function __construct(CategoryRepository $categoryRepository, OperationRepository $operationRepository, PaginatorInterface $paginator)
    {
        $this->categoryRepository = $categoryRepository;
        $this->operationRepository = $operationRepository;
        $this->paginator = $paginator;
    }// end __construct()

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function createPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->categoryRepository->queryAll(),
            $page,
            CategoryRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }// end createPaginatedList()

    /**
     * Find by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Category
    {
        return $this->categoryRepository->findOneById($id);
    }// end findOneById()

    /**
     * Find by title.
     *
     * @param string $title Category title
     *
     * @return Category|null Category entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneByTitle(string $title): ?Category
    {
        return $this->categoryRepository->findOneByTitle($title);
    }// end findOneByTitle()

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $this->categoryRepository->save($category);
    }// end save()

    /**
     * Delete entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }// end delete()

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function canBeDeleted(Category $category): bool
    {
        $result = $this->operationRepository->countByCategory($category);

        return !($result > 0);
    }// end canBeDeleted()
}// end class
