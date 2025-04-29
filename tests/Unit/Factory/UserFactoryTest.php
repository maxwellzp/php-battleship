<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserFactory::class)]
class UserFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectUser()
    {
        $email = "battleship-tester@gmail.com";
        $password = "password";

        $factory = new UserFactory();
        $user = $factory->create($email, $password);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(0, $user->getLosses());
        $this->assertEquals(0, $user->getWins());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNotEmpty($user->getUsername());
        $this->assertStringContainsString("player-", $user->getUsername());
    }
}
