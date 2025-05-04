<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

class UserFactory
{
    public function create(string $email, string $plainPassword, string $username = ""): User
    {
        if ($username === "") $username = 'player-' . $email;

        return new User($email, $plainPassword, $username);
    }
}
