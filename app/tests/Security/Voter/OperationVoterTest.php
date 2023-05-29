<?php
//
//namespace App\Tests\Security\Voter;
//
//use App\Security\Voter\OperationVoter;
//use PHPUnit\Framework\TestCase;
//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
//use Symfony\Component\Security\Core\User\UserInterface;
//
//class OperationVoterTest extends TestCase
//{
//    protected TokenInterface $token;
//
//    public function setUp(): void
//    {
//        $this->token = $this->createMock(TokenInterface::class);
//        $user = $this->createMock(UserInterface::class);
//
//        $this->token
//            ->method('getUser')
//            ->willReturn($user);
//    }
//
//    public function testVote(array $attributes, string $subject, ?TokenInterface $token, int $expected): void
//    {
//        $voter = new OperationVoter();
//    }
//}
