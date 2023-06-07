<?php
/**
 * User controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private const TEST_ROUTE = '/user';

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
     * Test index action as anonymous user.
     */
    public function testIndexActionAsAnonymousUser(): void
    {
        $this->httpClient->request('GET', '/user/');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test index action as logged in admin.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexActionAsLoggedInAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'user_index_admin@example.com');
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
    * Test show action.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowAction(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_user_show_admin@example.com');
        $this->httpClient->loginUser($adminUser);
        $adminUserId = $adminUser->getId();
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUserId);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
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
