<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


trait UserTestTrait
{
    protected User $player1;
    protected User $player2;

    protected UserFactory $userFactory;
    protected UserRepository $userRepository;

    protected function bootUserTestKernel(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->userFactory = $container->get(UserFactory::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    protected function createUsers(): void
    {
        $this->player1 = $this->userFactory->create('player1@example.com', 'password');
        $this->userRepository->save($this->player1, true);

        $this->player2 = $this->userFactory->create('player2@example.com', 'password');
        $this->userRepository->save($this->player2, true);
    }
}