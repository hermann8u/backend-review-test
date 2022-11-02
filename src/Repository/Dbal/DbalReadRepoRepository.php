<?php

declare(strict_types=1);

namespace App\Repository\Dbal;

use App\Repository\ReadRepoRepository;
use Doctrine\DBAL\Connection;

final class DbalReadRepoRepository implements ReadRepoRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM repo
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id,
        ]);

        return (bool) $result;
    }
}
