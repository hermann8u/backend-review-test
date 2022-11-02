<?php

declare(strict_types=1);

namespace App\Repository\Memoized;

use App\Repository\ReadRepoRepository;
use Symfony\Contracts\Service\ResetInterface;

final class MemoizedReadRepoRepository implements ReadRepoRepository, ResetInterface
{
    /** @var array<int, bool> */
    private array $existMap = [];

    public function __construct(
        private readonly ReadRepoRepository $inner,
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
