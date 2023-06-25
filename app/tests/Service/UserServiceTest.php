<?php

namespace App\Tests\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TypeError;

class UserServiceTest extends KernelTestCase
{
    /**
     * Test upgradePassword throw TypeError.
     *
     *@throws \TypeError|\Exception|ORMException|OptimisticLockException|NonUniqueResultException|ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public function testUpgradePasswordThrowTypeError(): void
    {
        $notUser = new class() {};

        $this->expectException(\TypeError::class);

        $userService = self::getContainer()->get('App\Service\UserService');
        $userService->upgradePassword($notUser, 'new_password');
    }
}
