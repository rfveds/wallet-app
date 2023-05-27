<?php
///**
//* Operation service tests.
// */
//
//namespace App\Tests\Service;
//
//use App\Entity\Category;
//use App\Entity\Enum\UserRole;
//use App\Entity\Operation;
//use App\Entity\Tag;
//use App\Entity\User;
//use App\Entity\Wallet;
//use App\Repository\CategoryRepository;
//use App\Repository\TagRepository;
//use App\Repository\UserRepository;
//use App\Repository\WalletRepository;
//use App\Service\OperationService;
//use App\Service\OperationServiceInterface;
//use Doctrine\DBAL\Types\Types;
//use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\ORM\Exception\ORMException;
//use Doctrine\ORM\OptimisticLockException;
//use Psr\Container\ContainerExceptionInterface;
//use Psr\Container\NotFoundExceptionInterface;
//use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
//
///**
// * Class OperationServiceTest.
// */
//class OperationServiceTest extends KernelTestCase
//{
//    /**
//     * Operation repository.
//     */
//    private ?EntityManagerInterface $entityManager;
//
//    /**
//     * Operation service.
//     */
//    private ?OperationServiceInterface $operationService;
//
//    /**
//     * Set up test.
//     */
//    public function setUp(): void
//    {
//        $container = static::getContainer();
//        $this->entityManager = $container->get('doctrine.orm.entity_manager');
//        $this->operationService = $container->get(OperationService::class);
//    }
//
//    /**
//     * Test save.
//     *
//     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
//     * @throws \Doctrine\ORM\ORMException
//     */
//    public function testSave(): void
//    {
//        // given
//        $expectedOperation = new Operation();
//        $expectedOperation->setAuthor($this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'operation1@example.com'));
//        $expectedOperation->setTitle('Test Operation 4');
//        $expectedOperation->setAmount('100.00');
//        $expectedOperation->setCreatedAt(new \DateTimeImmutable('now'));
//        $expectedOperation->setUpdatedAt(new \DateTimeImmutable('now'));
//        $expectedOperation->addTag($this->createTag());
//        $expectedOperation->setWallet($this->createWallet());
//        $expectedOperation->setCategory($this->createCategory());
//
//        // when
//        $this->operationService->save($expectedOperation);
//
//        // then
//        $expectedOperationId = $expectedOperation->getId();
//        $resultOperation = $this->entityManager->createQueryBuilder()
//            ->select('operation')
//            ->from(Operation::class, 'operation')
//            ->where('operation.id = :id')
//            ->setParameter(':id', $expectedOperationId, Types::INTEGER)
//            ->getQuery()
//            ->getSingleResult();
//
//        $this->assertEquals($expectedOperation, $resultOperation);
//    }
//
//    /**
//     * Create Wallet.
//     *
//     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
//     * @throws \Doctrine\ORM\ORMException
//     */
//    private function createWallet(): Wallet
//    {
//        $wallet = new Wallet();
//        $wallet->setTitle('Test Wallet 3');
//        $wallet->setType('cash');
//        $wallet->setBalance('999');
//        $wallet->setUser($this->createUser([UserRole::ROLE_USER->value], 'operation@example.com'));
//        $walletRepository = self::getContainer()->get(WalletRepository::class);
//        $walletRepository->save($wallet);
//
//        return $wallet;
//    }
//
//    /**
//     * Create Category.
//     */
//    private function createCategory(): Category
//    {
//        $category = new Category();
//        $category->setTitle('Test Category 4');
//        $category->setCreatedAt(new \DateTimeImmutable('now'));
//        $category->setCreatedAt(new \DateTimeImmutable('now'));
//        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
//        $categoryRepository->save($category);
//
//        return $category;
//    }
//
//    /**
//     * Create Tag.
//     */
//    private function createTag(): Tag
//    {
//        $tag = new Tag();
//        $tag->setTitle('Test Tag 4');
//        $tag->setCreatedAt(new \DateTimeImmutable('now'));
//        $tag->setUpdatedAt(new \DateTimeImmutable('now'));
//        $tagRepository = self::getContainer()->get(TagRepository::class);
//        $tagRepository->save($tag);
//
//        return $tag;
//    }
//
//    /**
//     * Create user.
//     *
//     * @param array $roles User roles
//     *
//     * @return User User entity
//     *
//     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|\Doctrine\ORM\ORMException|OptimisticLockException
//     */
//    private function createUser(array $roles, string $email): User
//    {
//        $passwordHasher = static::getContainer()->get('security.password_hasher');
//        $user = new User();
//        $user->setEmail($email);
//        $user->setRoles($roles);
//        $user->setPassword(
//            $passwordHasher->hashPassword(
//                $user,
//                'p@55w0rd'
//            )
//        );
//        $userRepository = static::getContainer()->get(UserRepository::class);
//        $userRepository->save($user);
//
//        return $user;
//    }
//}
