<?php
/**
* Category service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use App\Service\OperationServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CategoryServiceTest.
 */
class CategoryServiceTest extends KernelTestCase
{
    /**
     * Category service.
     *
     * @var CategoryService|null
     */
    private ?CategoryServiceInterface $categoryService;

    /**
     * Operation service.
     */
    private ?OperationServiceInterface $operationService;

    /**
     * Category repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryService = $container->get(CategoryService::class);
        $this->operationService = $container->get(OperationServiceInterface::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException|OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testSave(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Category');
        $expectedCategory->setAuthor($this->createUser(['ROLE_USER'], 'test_save@example.com'));
        $expectedCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedCategory->setSlug('test-category');

        // when
        $this->categoryService->save($expectedCategory);

        // then
        $expectedCategoryId = $expectedCategory->getId();
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test delete.
     *
     * @throws ORMException|OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testDelete(): void
    {
        // given
        $categoryToDelete = new Category();
        $categoryToDelete->setTitle('Test Delete Category');
        $categoryToDelete->setAuthor($this->createUser(['ROLE_USER'], 'test_delete_cat@example.com'));
        $categoryToDelete->setCreatedAt(new \DateTimeImmutable('now'));
        $categoryToDelete->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($categoryToDelete);
        $this->entityManager->flush();
        $deletedCategoryId = $categoryToDelete->getId();

        // when
        $this->categoryService->delete($categoryToDelete);

        // then
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $deletedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultCategory);
    }

    /**
     * Test find by id.
     *
     * @throws NonUniqueResultException|ORMException|OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testFindById(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Find By Id Category');
        $expectedCategory->setAuthor($this->createUser(['ROLE_USER'], 'find_id_cat@example.com'));
        $expectedCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();
        $expectedCategoryId = $expectedCategory->getId();

        // when
        $resultCategory = $this->categoryService->findOneById($expectedCategoryId);

        // then
        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test find by title.
     *
     * @throws NonUniqueResultException|ORMException|OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface

     */
    public function testFindByTitle(): void
    {
        // given
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test Find By Title Category');
        $expectedCategory->setAuthor($this->createUser(['ROLE_USER'], 'user_find_cat@example.com'));
        $expectedCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($expectedCategory);
        $this->entityManager->flush();
        $expectedCategoryTitle = $expectedCategory->getTitle();

        // when
        $resultCategory = $this->categoryService->findOneByTitle($expectedCategoryTitle);

        // then
        $this->assertEquals($expectedCategory, $resultCategory);
    }

    /**
     * Test pagination.
     */
    public function testCreatePaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $category = new Category();
            $category->setTitle('Test Category #'.$counter);
            $category->setAuthor($this->createUser(['ROLE_USER'], 'user_test_create_cat'.$counter.'@example.com'));
            $category->setCreatedAt(new \DateTimeImmutable('now'));
            $category->setUpdatedAt(new \DateTimeImmutable('now'));
            $this->categoryService->save($category);

            ++$counter;
        }

        // when
        $result = $this->categoryService->createPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    protected function createUser(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }
    // other tests for paginated list
}
