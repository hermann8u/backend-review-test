<?php

declare(strict_types=1);

namespace App\Repository\Memoized;

use App\Repository\ReadActorRepository;
use Symfony\Contracts\Service\ResetInterface;

final class MemoizedReadActorRepository implements ReadActorRepository, ResetInterface
{
    /** @var array<int, bool> */
    private array $existMap = [];

    public function __construct(
        private readonly ReadActorRepository $inner,
    ) {
    }

    public function exist(int $id): bool
    {
        return $this->existMap[$id] ??= $this->inner->exist($id);
    }

    public function reset(): void
    {
        $this->existMap = [];
    }
}
