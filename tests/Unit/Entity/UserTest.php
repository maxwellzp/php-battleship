<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    public function testConstructorAndGettersWorkingCorrectlyWithDefaultUsername()
    {
        $email = "battleship-tester@gmail.com";
        $password = "password";

        $user = new User($email, $password);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(0, $user->getLosses());
        $this->assertEquals(0, $user->getWins());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNotEmpty($user->getUsername());
        $this->assertStringContainsString("player-", $user->getUsername());
    }

    public function testConstructorAndGettersWorkingCorrectlyWithUsername()
    {
        $email = "battleship-tester@gmail.com";
        $password = "password";
        $userName = "John";

        $user = new User($email, $password, $userName);

        $this->assertEquals($userName, $user->getUsername());
    }
}