<?php
/**
* Tag service tests.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TagService;
use App\Service\TagServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends KernelTestCase
{
    /**
     * Tag repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Tag service.
     */
    private ?TagServiceInterface $tagService;

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
        $this->tagService = $container->get(TagService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException|OptimisticLockException|NonUniqueResultException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testSave(): void
    {
        // given
        $expectedTag = new Tag();
        $expectedTag->setTitle('Test Tag');
        $expectedTag->setAuthor($this->createUser(['ROLE_USER'], 'user_test_create_tag@example.com'));

        // when
        $this->tagService->save($expectedTag);

        // then
        $expectedTagId = $expectedTag->getId();
        $resultTag = $this->entityManager->createQueryBuilder()
            ->select('tag')
            ->from(Tag::class, 'tag')
            ->where('tag.id = :id')
            ->setParameter('id', $expectedTagId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedTag, $resultTag);
    }

    /**
     * Test delete.
     *
     * @throws ORMException|OptimisticLockException|NonUniqueResultException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testDelete(): void
    {
        // given
        $tagToDelete = new Tag();
        $tagToDelete->setTitle('Test Tag');
        $tagToDelete->setAuthor($this->createUser(['ROLE_USER'], 'test_delete_tag_user@example.com'));
        $this->entityManager->persist($tagToDelete);
        $this->entityManager->flush();
        $tagToDeleteId = $tagToDelete->getId();

        // when
        $this->tagService->delete($tagToDelete);

        // then
        $resultTag = $this->entityManager->createQueryBuilder()
            ->select('tag')
            ->from(Tag::class, 'tag')
            ->where('tag.id = :id')
            ->setParameter('id', $tagToDeleteId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultTag);
    }

    /**
     * Test pagination.
     *
     * @throws ORMException|OptimisticLockException|NonUniqueResultException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testCreatePaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $tag = new Tag();
            $tag->setTitle('Test Tag #'.$counter);
            $tag->setAuthor($this->createUser(['ROLE_USER'], 'test_pagination_tag'.$counter.'@example.com'));
            $this->tagService->save($tag);

            ++$counter;
        }

        // when
        $result = $this->tagService->createPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, count($result));
    }

    /**
     * Test find by id.
     *
     * @throws NonUniqueResultException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException|OptimisticLockException
     */
    public function testFindById(): void
    {
        // given
        $expectedTag = new Tag();
        $expectedTag->setTitle('Test Tag 1');
        $expectedTag->setAuthor($this->createUser(['ROLE_USER'], 'test_find_id_tag@example.com'));
        $this->entityManager->persist($expectedTag);
        $this->entityManager->flush();
        $expectedTagId = $expectedTag->getId();

        // when
        $resultTag = $this->tagService->findOneById($expectedTagId);

        // then
        $this->assertEquals($expectedTag, $resultTag);
    }

    /**
     * Test find by title.
     *
     * @throws NonUniqueResultException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException|OptimisticLockException
     */
    public function testFindByTitle(): void
    {
        // given
        $expectedTag = new Tag();
        $expectedTag->setTitle('Test Tag 2');
        $expectedTag->setAuthor($this->createUser(['ROLE_USER'], 'test_find_title_tag@example.com'));
        $this->entityManager->persist($expectedTag);
        $this->entityManager->flush();
        $expectedTagTitle = $expectedTag->getTitle();

        // when
        $resultTag = $this->tagService->findOneByTitle($expectedTagTitle);

        // then
        $this->assertEquals($expectedTag, $resultTag);
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
    protected function createUser(array $roles, string $email): User
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
        $userRepository->save($user);

        return $user;
    }
}
