<?php
/**
* Operation service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use App\Service\OperationService;
use App\Service\OperationServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OperationServiceTest.
 */
class OperationServiceTest extends KernelTestCase
{
    /**
     * Operation repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Operation service.
     */
    private ?OperationServiceInterface $operationService;

    /**
     * Set up test.
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->operationService = $container->get(OperationService::class);
    }

    /**
     * Test save.
     *
     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedOperation = new Operation();
        $author = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'operation_save@example.com');
        $expectedOperation->setAuthor($author);
        $expectedOperation->setTitle('Test Operation Save');
        $expectedOperation->setAmount('100.00');
        $expectedOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->addTag($this->createTag('save'));
        $expectedOperation->setWallet($this->createWallet($author, 'save'));
        $expectedOperation->setCategory($this->createCategory('save'));

        // when
        $this->operationService->save($expectedOperation);

        // then
        $expectedOperationId = $expectedOperation->getId();
        $resultOperation = $this->entityManager->createQueryBuilder()
            ->select('operation')
            ->from(Operation::class, 'operation')
            ->where('operation.id = :id')
            ->setParameter(':id', $expectedOperationId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedOperation, $resultOperation);
    }

    /**
     * Test delete.
     *
     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    public function testDelete(): void
    {
        // given
        $operationToDelete = new Operation();
        $author = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'operation_delete@example.com');
        $operationToDelete->setAuthor($author);
        $operationToDelete->setTitle('Test Operation Delete');
        $operationToDelete->setAmount('100.00');
        $operationToDelete->setCreatedAt(new \DateTimeImmutable('now'));
        $operationToDelete->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationToDelete->addTag($this->createTag('delete'));
        $operationToDelete->setWallet($this->createWallet($author, 'delete'));
        $operationToDelete->setCategory($this->createCategory('delete'));

        $this->entityManager->persist($operationToDelete);
        $this->entityManager->flush();
        $deletedOperationId = $operationToDelete->getId();

        // when
        $this->operationService->delete($operationToDelete);

        // when
        $resultOperation = $this->entityManager->createQueryBuilder()
            ->select('operation')
            ->from(Operation::class, 'operation')
            ->where('operation.id = :id')
            ->setParameter('id', $deletedOperationId)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultOperation);
    }

    /**
     * Test pagination.
     *
     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    public function testCreatePagination(): void
    {
        // given
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;

        $author = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'operation_pagination@example.com');
        $counter = 0;
        while ($counter < $dataSetSize) {
            $operation = new Operation();
            $operation->setAuthor($author);
            $operation->setTitle('Test Operation Delete #'.$counter);
            $operation->setAmount('100.00');
            $operation->setCreatedAt(new \DateTimeImmutable('now'));
            $operation->setUpdatedAt(new \DateTimeImmutable('now'));
            $operation->addTag($this->createTag('delete'.$counter));
            $operation->setWallet($this->createWallet($author, 'delete'.$counter));
            $operation->setCategory($this->createCategory('delete'.$counter));
            $this->operationService->save($operation);

            ++$counter;
        }

        // when
        $result = $this->operationService->createPaginatedList($page, $author);

        // then
        $this->assertEquals($expectedResultSize, $result->count());


    }

    /**
     * Create Wallet.
     *
     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
     * @throws \Doctrine\ORM\ORMException
     */
    private function createWallet($author, $walletTitle): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($walletTitle);
        $wallet->setType('cash');
        $wallet->setBalance('999');
        $wallet->setUser($author);
        $walletRepository = self::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet);

        return $wallet;
    }

    /**
     * Create Category.
     */
    private function createCategory($categoryTitle): Category
    {
        $category = new Category();
        $category->setTitle($categoryTitle);
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create Tag.
     */
    private function createTag($tagTitle): Tag
    {
        $tag = new Tag();
        $tag->setTitle($tagTitle);
        $tag->setCreatedAt(new \DateTimeImmutable('now'));
        $tag->setUpdatedAt(new \DateTimeImmutable('now'));
        $tagRepository = self::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|\Doctrine\ORM\ORMException|OptimisticLockException
     */
    protected function createUser(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
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
}
