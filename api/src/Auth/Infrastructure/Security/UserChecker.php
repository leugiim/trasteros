<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Users\Domain\Model\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Rejects inactive users at the firewall, so a JWT issued before a user was
 * deactivated stops working on every route (not only on #[Auth] ones).
 */
final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && !$user->isActivo()) {
            throw new CustomUserMessageAccountStatusException('El usuario está desactivado');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
