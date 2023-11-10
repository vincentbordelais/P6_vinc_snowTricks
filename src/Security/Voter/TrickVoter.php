<?php

namespace App\Security\Voter;

use App\Entity\Trick;
use App\Entity\User;
// use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TrickVoter extends Voter
{
    public const TRICK_EDIT = 'TRICK_EDIT';
    public const TRICK_DELETE = 'TRICK_DELETE';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $trick): bool // vérifie que la permission qu'on demande existe au niveau de ce votant
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::TRICK_EDIT, self::TRICK_DELETE]) // est-ce que les attributes sont bien dans la liste [self::TRICK_EDIT, self::TRICK_DELETE]?
            && $trick instanceof \App\Entity\Trick;
    }
    protected function voteOnAttribute(string $attribute, $trick, TokenInterface $token): bool // boolean, vérifie si on respecte tous les critères demandés pour les accès
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) { // si user est connecté
            return false;
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        } // si l'utilisateur est l'admin, il a accès à tout, avec ce voter
        if (null === $trick->getAuthor()) {
            return false;
        } // si le trick a un auteur
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::TRICK_EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return $this->canEdit($trick, $user);
                break;
            case self::TRICK_DELETE:
                // logic to determine if the user can VIEW
                // return true or false
                return $this->canDelete($trick, $user);
                break;
        }
        return false;
    }

    private function canEdit(Trick $trick, User $user)
    {
        return $user === $trick->getAuthor();
    }

    private function canDelete(Trick $trick, User $user)
    {
        return $user === $trick->getAuthor();
    }
}
