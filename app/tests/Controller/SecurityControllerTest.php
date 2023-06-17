<?php
/**
 * Security controller tests.
 */

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test login route for anonymous user.
     */
    public function testLoginRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $expectedTitle = 'action.login';

        // when
        $client = static::createClient();
        $client->request('GET', '/login');
        $resultStatusCode = $client->getResponse()->getStatusCode();
        $resultTitle = $client->getResponse()->getContent();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertStringContainsString($expectedTitle, $resultTitle);
    }

    /**
     * Test logout route for logged user.
     */
    public function testLogoutRouteLoggedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $expectedRoute = '/login';

        // when
        $client = static::createClient();
        $client->request('GET', '/logout');
        $resultStatusCode = $client->getResponse()->getStatusCode();
        $resultRoute = $client->getResponse()->headers->get('location');

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertStringContainsString($expectedRoute, $resultRoute);
    }

    /**
     * Test failed authentication.
     */
    public function testFailedAuthentication(): void
    {
        $client = static::createClient();

        $client->request('POST', '/login', [
            'email' => 'testuser',
            'password' => 'testpassword',
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $client->getResponse()->headers->get('Location'));
    }

    /**
     * Test successful authentication.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testSuccessfulAuthentication(): void
    {
        // given
        $client = static::createClient();

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_admin_login@example.com');
        $client->loginUser($adminUser);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test_admin_login@example.com');

        // when
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/');
        $divElement = $crawler->filter('a:contains("test_admin_login@example.com")');

        // then
        $this->assertCount(1, $divElement);
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test logging out.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testLogout(): void
    {
        // given
        $client = static::createClient();
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_admin_logout@example.com');
        $client->loginUser($adminUser);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test_admin_login@example.com');

        // when
        $client->loginUser($testUser);
        $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/');
        $divElement = $crawler->filter('div:contains("test_admin_login@example.com")');

        // then
        $this->assertCount(0, $divElement);
    }

    /**
     * Test logout throws logic exception.
     */
    public function testLogoutThrowsLogicException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $controller->logout();
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
