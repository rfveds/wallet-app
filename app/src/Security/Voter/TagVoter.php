<?php
/**
 * Tag Voter.
 */

namespace App\Security\Voter;

use App\Entity\Tag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TagVoter.
 */
class TagVoter extends Voter
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
     * Security helper.
     *
     * @var Security Security helper
     */
    private Security $security;

    /**
     * TagVoter constructor.
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
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Tag;
    }// end supports()

    /**
     * Perform a single access check tag on a given attribute, subject and token.
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
            default => false,
        };
    }// end voteOnAttribute()

    /**
     * Check if user can edit tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User entity
     *
     * @return bool Vote result
     */
    private function canEdit(Tag $tag, UserInterface $user): bool
    {
        return $tag->getAuthor() === $user || $this->security->isGranted('ROLE_ADMIN');
    }// end canEdit()

    /**
     * Check if user can view tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User entity
     *
     * @return bool Vote result
     */
    private function canView(Tag $tag, UserInterface $user): bool
    {
        return
            $tag->getAuthor() === $user ||
            $this->security->isGranted('ROLE_ADMIN') ||
            $tag->getAuthor()->getRoles() === ['ROLE_ADMIN'];
    }// end canView()

    /**
     * Check if user can delete tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User entity
     *
     * @return bool Vote result
     */
    private function canDelete(Tag $tag, UserInterface $user): bool
    {
        return $tag->getAuthor() === $user || $this->security->isGranted('ROLE_ADMIN');
    }// end canDelete()
}// end class
