<?php
/**
* Report Controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Report;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\ReportRepository;
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
 * Class ReportControllerTest.
 */
class ReportControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string TEST_ROUTE
     */
    public const TEST_ROUTE = '/report';

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
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_report_admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * Test show single report.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSingleReport(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_report_show@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedReport = new Report();
        $expectedReport->setTitle('Test report');
        $expectedReport->setCategory($this->createCategory('Test Category Report', $adminUser));
        $expectedReport->setTag($this->createTag('Test Tag Report', $adminUser));
        $expectedReport->setWallet($this->createWallet('Test Wallet Report', $adminUser));
        $expectedReport->setDateFrom(new \DateTimeImmutable('2020-01-01'));
        $expectedReport->setDateTo(new \DateTimeImmutable('2020-01-31'));
        $expectedReport->setCreatedAt(new \DateTimeImmutable('2020-01-01'));
        $expectedReport->setUpdatedAt(new \DateTimeImmutable('2020-01-01'));
        $expectedReport->setTag($this->createTag('Test Tag Report', $adminUser));
        $expectedReport->setAuthor($adminUser);
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $reportRepository->save($expectedReport, true);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedReport->getId());
        $result = $this->httpClient->getResponse();

        // then
        $report = $reportRepository->findOneBy(['id' => $expectedReport->getId()]);
        $this->assertNotNull($report);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedReport->getTitle());
    }

    /**
     * Test show single report for unauthorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowReportForUnauthorizedUser(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_report_unauth@example.com');
        $this->httpClient->loginUser($user);

        $reportUser = $this->createUser([UserRole::ROLE_USER->value], 'report_owner@example.com');
        $report = new Report();
        $report->setTitle('unauthorized report');
        $report->setCategory($this->createCategory('Test Category Report Unauthorized', $reportUser));
        $report->setTag($this->createTag('Test Tag Report Unauthorized', $reportUser));
        $report->setWallet($this->createWallet('Test Wallet Report Unauthorized', $reportUser));
        $report->setDateFrom(new \DateTimeImmutable('2020-01-01'));
        $report->setDateTo(new \DateTimeImmutable('2020-01-31'));
        $report->setAuthor($reportUser);
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $reportRepository->save($report, true);
        $reportId = $reportRepository->findOneBy(['title' => 'unauthorized report']);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$reportId->getId());

        // then
        $result = $this->httpClient->getResponse();
        $this->assertEquals(403, $result->getStatusCode());
    }

    /**
     * Test create report.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateReport(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_create_report_with_no_value@example.com');
        $this->httpClient->loginUser($user);
        $reportTitle = 'Test create report';
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'utwórz',
            ['report' => [
                'title' => $reportTitle,
            ],
            ]);

        // then
        $savedReport = $reportRepository->findOneBy(['title' => $reportTitle]);
        $this->assertNull($savedReport);

        $result = $this->httpClient->getResponse();
        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * Test create report with category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateReportWithCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_create_report_cat@example.com');
        $this->httpClient->loginUser($user);
        $reportTitle = 'Test create report';
        $reportCategory = $this->createCategory('Test Category Report Create', $user);
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'utwórz',
            ['report' => [
                'title' => $reportTitle,
                'category' => $reportCategory->getId(),
            ],
        ]);

        // then
        $savedReport = $reportRepository->findOneBy(['title' => $reportTitle]);
        $this->assertEquals($reportTitle, $savedReport->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test create report with wallet.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateReportWithWallet(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_create_report_wallet@example.com');
        $this->httpClient->loginUser($user);
        $reportTitle = 'Test create report wallet';
        $reportWallet = $this->createWallet('Test Wallet Report Create', $user);
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'utwórz',
            ['report' => [
                'title' => $reportTitle,
                'wallet' => $reportWallet->getId(),
            ],
            ]);

        // then
        $savedReport = $reportRepository->findOneBy(['title' => $reportTitle]);
        $this->assertEquals($reportTitle, $savedReport->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test create report with tag.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateReportWithTag(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_create_report_tag@example.com');
        $this->httpClient->loginUser($user);
        $reportTitle = 'Test create report tag';
        $reportTag = $this->createTag('Test Tag Report Create', $user);
        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'utwórz',
            ['report' => [
                'title' => $reportTitle,
                'tag' => $reportTag->getId(),
            ],
            ]);

        // then
        $savedReport = $reportRepository->findOneBy(['title' => $reportTitle]);
        $this->assertEquals($reportTitle, $savedReport->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit report.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditReport(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'edit_report_user@example.com');
        $this->httpClient->loginUser($user);

        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $testReport = new Report();
        $testReport->setTitle('Test edit report');
        $testReport->setCategory($this->createCategory('Test Category Report Edit', $user));
        $testReport->setTag($this->createTag('Test Tag Report Edit', $user));
        $testReport->setWallet($this->createWallet('Test Wallet Report Edit', $user));
        $testReport->setDateFrom(new \DateTimeImmutable('2020-01-01'));
        $testReport->setDateTo(new \DateTimeImmutable('2020-01-31'));
        $testReport->setAuthor($user);
        $reportRepository->save($testReport, true);
        $testReportId = $reportRepository->findOneBy(['title' => 'Test edit report']);
        $expectedNewReportTitle = 'Test edit report new title';

        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'.$testReportId->getId().'/edit'
        );

        // when
        $this->httpClient->submitForm(
            'edytuj',
            ['report' => [
                'title' => $expectedNewReportTitle,
            ],
        ]);

        // then
        $savedReport = $reportRepository->findOneBy(['title' => $expectedNewReportTitle]);
        $this->assertEquals($expectedNewReportTitle, $savedReport->getTitle());

        $this->assertNotNull($savedReport->getCreatedAt());
        $this->assertNotNull($savedReport->getUpdatedAt());
    }

    /**
     * Test delete report.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteReport(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_delete_report_user@example.com');
        $this->httpClient->loginUser($user);

        $reportRepository = static::getContainer()->get(ReportRepository::class);
        $testReport = new Report();
        $testReport->setTitle('Test delete report');
        $testReport->setCategory($this->createCategory('Test Category Report Delete', $user));
        $testReport->setTag($this->createTag('Test Tag Report Delete', $user));
        $testReport->setWallet($this->createWallet('Test Wallet Report Delete', $user));
        $testReport->setDateFrom(new \DateTimeImmutable('2020-01-01'));
        $testReport->setDateTo(new \DateTimeImmutable('2020-01-31'));
        $testReport->setAuthor($user);
        $reportRepository->save($testReport, true);
        $testReportId = $reportRepository->findOneBy(['title' => 'Test delete report']);

        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'.$testReportId->getId().'/delete'
        );

        // when
        $this->httpClient->submitForm('usuń');

        // then
        $savedReport = $reportRepository->findOneBy(['title' => 'Test delete report']);
        $this->assertNull($savedReport);
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
    private function createWallet(string $title, User $user, string $balance = '0'): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($title);
        $wallet->setBalance($balance);
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
    private function createCategory(string $title, User $user): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setUserOrAdmin('user');
        $category->setAuthor($user);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create tag.
     *
     * @throws ContainerExceptionInterface
     */
    private function createTag(string $string, User $user): Tag
    {
        $tag = new Tag();
        $tag->setTitle($string);
        $tag->setAuthor($user);
        $tag->setUserOrAdmin('user');
        $tagRepository = self::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }
}
