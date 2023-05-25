<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Tests\Service;

use App\Entity\Wallet;
use App\Service\WalletService;
use App\Service\WalletServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class WalletServiceTest.
 */
class WalletServiceTest extends KernelTestCase
{
    /**
     * Wallet repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Wallet service.
     */
    private ?WalletServiceInterface $walletService;

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
        $this->walletService = $container->get(WalletService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedWallet = new Wallet();
        $expectedWallet->setTitle('Test Wallet');
        $expectedWallet->setBalance(0);
        $expectedWallet->setType('cash');

        // when
        $this->walletService->save($expectedWallet);

        // then
        $expectedWalletId = $expectedWallet->getId();
        $resultWallet = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter('id', $expectedWalletId)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedWallet, $resultWallet);
    }


    /**
     * Test delete.
     *
     * @throws ORMException
     */
    public function testDelete(): void
    {
        // given
        $walletToDelete = new Wallet();
        $walletToDelete->setTitle('Test Wallet');
        $walletToDelete->setBalance(0);
        $walletToDelete->setType('cash');

        $this->entityManager->persist($walletToDelete);
        $this->entityManager->flush();
        $deletedWalletId = $walletToDelete->getId();

        // when
        $this->walletService->delete($walletToDelete);

        // then
        $resultWallet = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter('id', $deletedWalletId)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultWallet);
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
            $wallet = new Wallet();
            $wallet->setTitle('Test Category #'.$counter);
            $wallet->setBalance(0);
            $wallet->setType('cash');

            $this->walletService->save($wallet);

            ++$counter;
        }

        // when
        $result = $this->walletService->createPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }
}
