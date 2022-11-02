<?php

namespace App\Repository\Dbal;

use App\Dto\SearchInput;
use App\Repository\ReadEventRepository;
use Doctrine\DBAL\Connection;

final class DbalReadEventRepository implements ReadEventRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function countAll(SearchInput $searchInput): int
    {
        $sql = <<<SQL
            SELECT sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
        SQL;

        return (int) $this->connection->fetchOne($sql, [
            'date' => $searchInput->date
        ]);
    }

    public function countByType(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, sum(count) AS count
            FROM event
            WHERE date(create_at) = :date
            AND type LIKE :keyword
            GROUP BY type
        SQL;

        return $this->connection->fetchAllKeyValue($sql, [
            'date' => $searchInput->date,
            'keyword' => '%' . $searchInput->keyword . '%',
        ]);
    }

    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT extract(hour from create_at) as hour, type, sum(count) as count
            FROM event
            WHERE date(create_at) = :date
            AND payload like %:keyword%
            GROUP BY TYPE, EXTRACT(hour from create_at)
        SQL;

        $stats = $this->connection->fetchAll($sql, [
            'date' => $searchInput->date,
            'keyword' => $searchInput->keyword,
        ]);

        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = $stat['count'];
        }

        return $data;
    }

    public function getLatest(SearchInput $searchInput): array
    {
        $sql = <<<SQL
            SELECT type, repo
            FROM event
            WHERE date(create_at) = :date
            AND payload like %{$searchInput->keyword}%
        SQL;

        $result = $this->connection->fetchAllAssociative($sql, [
            'date' => $searchInput->date,
            'keyword' => $searchInput->keyword,
        ]);

        $result = array_map(static function ($item) {
            $item['repo'] = json_decode($item['repo'], true);

            return $item;
        }, $result);

        return $result;
    }

    public function exist(int $id): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM event
            WHERE id = :id
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'id' => $id
        ]);

        return (bool) $result;
    }
}
