<?php
/**
 * Tag controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/tag';

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
        $expectedStatusCode = 301;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'tag_user_index@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowTag(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tag_user_show@exmaple.com');
        $this->httpClient->loginUser($adminUser);

        $expectedTag = new Tag();
        $expectedTag->setTitle('Test tag');
        $expectedTag->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedTag->setUpdatedAt(new \DateTimeImmutable('now'));
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagRepository->save($expectedTag);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedTag->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('dd', $expectedTag->getId());
        // ... more assertions...
    }

    /**
     * Test create tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateTag(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tag_user_create@example.com');
        $this->httpClient->loginUser($adminUser);
        $tagTitle = 'Test tag';
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');

        // when
        $this->httpClient->submitForm('action.save', [
            'tag' => ['title' => $tagTitle],
        ]);

        // then
        $savedTag = $tagRepository->findOneBy(['title' => $tagTitle]);
        $this->assertEquals($tagTitle, $savedTag->getTitle());

        $response = $this->httpClient->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test edit tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditTag(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tag_user_edit@example.com');
        $this->httpClient->loginUser($adminUser);
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagTitle = 'Test tag edit';
        $testTag = new Tag();
        $testTag->setTitle($tagTitle);
        $testTag->setCreatedAt(new \DateTimeImmutable('now'));
        $testTag->setUpdatedAt(new \DateTimeImmutable('now'));
        $tagRepository->save($testTag);
        $testTagId = $testTag->getId();
        $expectedNewTagTitle = 'Test tag edit updated';

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testTagId.'/edit');

        // when
        $this->httpClient->submitForm('action.edit', [
            'tag' => ['title' => $expectedNewTagTitle],
        ]);

        // then
        $savedTag = $tagRepository->findOneBy(['title' => $expectedNewTagTitle]);
        $this->assertEquals($expectedNewTagTitle, $savedTag->getTitle());
    }

    /**
     * Test delete tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteTag(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'tag_user_delete@example.com');
        $this->httpClient->loginUser($adminUser);
        $tagRepository = static::getContainer()->get(TagRepository::class);
        $tagTitle = 'Test tag delete';
        $testTag = new Tag();
        $testTag->setTitle($tagTitle);
        $testTag->setCreatedAt(new \DateTimeImmutable('now'));
        $testTag->setUpdatedAt(new \DateTimeImmutable('now'));
        $tagRepository->save($testTag);
        $testTagId = $testTag->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testTagId.'/delete');

        // when
        $this->httpClient->submitForm('action.delete');

        // then
        $this->assertNull($tagRepository->findOneBy(['title' => $tagTitle]));
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
}
