<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    public function __construct(private readonly HubInterface $hub)
    {

    }
}
