<?php
/**
 * User Voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter.
 */
class UserVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * List permission.
     */
    public const LIST = 'LIST';

    /**
     * Security helper.
     *
     * @var Security Security helper
     */
    private Security $security;

    /**
     * UserVoter constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }// end __construct()

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute Attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::LIST])
            && $subject instanceof User;
    }// end supports()

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::LIST => $this->canList($subject, $user),
            default => false,
        };
    }// end voteOnAttribute()

    /**
     * Can user edit user.
     *
     * @param UserInterface $subject User entity
     * @param UserInterface $user    User entity
     *
     * @return bool Vote result
     */
    private function canEdit(UserInterface $subject, UserInterface $user): bool
    {
        return
            $this->security->isGranted('ROLE_ADMIN') ||
            $subject->getId() === $user->getId();
    }// end canEdit()

    /**
     * Can user view user.
     *
     * @param UserInterface $subject User entity
     * @param UserInterface $user    User entity
     *
     * @return bool Vote result
     */
    private function canView(UserInterface $subject, UserInterface $user): bool
    {
        return
            $this->security->isGranted('ROLE_ADMIN') ||
            $subject->getId() === $user->getId();
    }// end canView()

    /**
     * Can user delete user.
     *
     * @param UserInterface $subject User entity
     * @param UserInterface $user    User entity
     *
     * @return bool Vote result
     */
    private function canDelete(UserInterface $subject, UserInterface $user): bool
    {
        return
            $this->security->isGranted('ROLE_ADMIN') ||
            $subject->getId() === $user->getId();
    }// end canDelete()

    /**
     * Can user list users.
     *
     * @return bool Vote result
     */
    private function canList(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }// end canList()
}// end class
