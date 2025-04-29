<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

class UserFactory
{
    public function create(string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername('player-' . $user->getEmail());
        $user->setPassword($plainPassword);
        $user->setWins(0);
        $user->setLosses(0);
        $user->setCreatedAt(new \DateTimeImmutable());
        return $user;
    }
}
