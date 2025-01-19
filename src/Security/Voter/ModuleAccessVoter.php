<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\Usuario;
use App\Security\UserProvider;
use Novosga\Settings\InstalledModule;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * ModuleAccessVoter
 * @extends Voter<string,InstalledModule>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final class ModuleAccessVoter extends Voter
{
    private const VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW]) && $subject instanceof InstalledModule;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Usuario || !$subject instanceof InstalledModule) {
            return false;
        }

        $roleName = UserProvider::roleName($subject->key);

        return $user->isAdmin() || in_array($roleName, $user->getRoles());
    }
}
