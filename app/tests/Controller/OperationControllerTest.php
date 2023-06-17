<?php
/**
 * Operation Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\OperationRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class OperationControllerTest.
 */
class OperationControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/operation';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302; // redirect to login page

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_operation_admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * Test show single operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowOperation(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_show_operation@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedOperation = new Operation();
        $expectedOperation->setTitle('Test 1 operation');
        $expectedOperation->setAmount(100);
        $expectedOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->setCategory($this->createCategory('testCategoryShowOperation'));
        $expectedOperation->setWallet($this->createWallet('wallet_show_operation', $adminUser));
        $expectedOperation->setAuthor($adminUser);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($expectedOperation);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedOperation->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedOperation->getTitle());
    }

    /**
     * Test create operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_create_operation@example.com');
        $this->httpClient->loginUser($user);
        $operationTitle = 'createdOperation';
        $category = $this->createCategory('testCategoryCreateOperation');
        $wallet = $this->createWallet('wallet_create_operation', $user);
        $tag = $this->createTag('testTagCreateOperation');
        $tag2 = $this->createTag('testTagCreateOperation2');
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'action.save',
            ['operation' => [
                'title' => $operationTitle,
                'amount' => '100',
                'category' => $category->getId(),
                'wallet' => $wallet->getId(),
                'tags' => 'testTagCreateOperation, testTagCreateOperation2',
                ],
            ]
        );

        // then
        $savedOperation = $operationRepository->findOneBy(['title' => $operationTitle]);
        $this->assertEquals($operationTitle, $savedOperation->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit operation.
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'operation_edit_user@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestEditOperation');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryEditOperation'));
        $testOperationWallet = $this->createWallet('wallet_edit_operation', $user);
        $testOperation->setWallet($testOperationWallet);
        $testOperation->setAuthor($user);
        $testOperation->addTag($this->createTag('testTagEditOperation'));
        $testOperation->addTag($this->createTag('testTagEditOperation2'));
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();
        $expectedNewOperationTitle = 'TestOperationEdited';

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testOperationId.'/edit');

        // when
        $this->httpClient->submitForm(
            'action.edit',
            ['operation' => [
                'title' => $expectedNewOperationTitle,
                'amount' => 200,
                'category' => 2,
                'wallet' => $testOperationWallet->getId(),
                'tags' => 'abc, def',
                ],
            ]
        );

        // then
        $savedOperation = $operationRepository->findOneById($testOperationId);
        $this->assertEquals($expectedNewOperationTitle, $savedOperation->getTitle());

        $this->assertNotNull($savedOperation->getUpdatedAt());
        $this->assertNotNull($savedOperation->getCreatedAt());
    }

    /**
     * Test remove tag from operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testRemoveTagFromOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_rm_tag_operation@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestRemoveTagOperation');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryRemoveTagOperation'));
        $testOperation->setWallet($this->createWallet('wallet_remove_tag', $user));
        $testOperation->setAuthor($user);
        $testTag = $this->createTag('testTagRemoveOperation');
        $testOperation->addTag($testTag);
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();

        // when
        $testOperation->removeTag($testTag);
        $operationRepository->save($testOperation);
        $testOperationTags = $testOperation->getTags();

        // then
        $this->assertEmpty($testOperationTags);
    }

    /**
     * Test delete operation.
     *
     * @throws NotFoundExceptionInterface|ORMException|ContainerExceptionInterface|OptimisticLockException
     */
    public function testDeleteOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_delete_operation_user@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestOperationDelete');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryDeleteOperation'));
        $testOperation->setWallet($this->createWallet('wallet_delete_operation', $user));
        $testOperation->setAuthor($user);
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testOperationId.'/delete');

        // when
        $this->httpClient->submitForm('action.delete');

        // then
        $savedOperation = $operationRepository->findOneById($testOperationId);
        $this->assertNull($savedOperation);
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
    private function createUser(array $roles, $email): User
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
        $userRepository->save($user, true);

        return $user;
    }

    /**
     * Create wallet.
     *
     * @param string $title Wallet name
     * @param User   $user  User entity
     *
     * @return Wallet Wallet entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createWallet(string $title, User $user): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($title);
        $wallet->setBalance(0);
        $wallet->setUser($user);
        $wallet->setType('cash');
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet, true);

        return $wallet;
    }

    /**
     * Create category.
     *
     * @throws ContainerExceptionInterface
     */
    private function createCategory($title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create tag.
     *
     * @throws ContainerExceptionInterface
     */
    private function createTag(string $string): Tag
    {
        $tag = new Tag();
        $tag->setTitle($string);
        $tagRepository = self::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }
}
