<?php

namespace App\Tests;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class WebBaseTestCase extends WebTestCase
{
    /**
     * Test client.
     */
    protected KernelBrowser $httpClient;

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
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
                'user1234'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    /**
     * Simulate user log in.
     *
     * @param User $user User entity
     */
    protected function logIn(User $user): void
    {
        $session = self::getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->httpClient->getCookieJar()->set($cookie);
    }

    /**
     * Create category.
     */
    protected function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('TCategory');
        $category->setUpdatedAt(new \DateTimeImmutable('now'));
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        try {
            $categoryRepository = self::getContainer()->get(CategoryRepository::class);
            $categoryRepository->save($category);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }

        return $category;
    }
}
