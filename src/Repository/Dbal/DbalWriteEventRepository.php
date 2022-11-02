<?php

namespace App\Repository\Dbal;

use App\Dto\EventInput;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Repository\ReadActorRepository;
use App\Repository\ReadEventRepository;
use App\Repository\ReadRepoRepository;
use App\Repository\WriteEventRepository;
use Doctrine\DBAL\Connection;

final class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ReadEventRepository $readEventRepository,
        private readonly ReadActorRepository $readActorRepository,
        private readonly ReadRepoRepository $readRepoRepository,
    ) {
    }

    public function add(Event $event): void
    {
        if ($this->readEventRepository->exist($event->id())) {
            return;
        }

        $this->addActor($event->actor());
        $this->addRepo($event->repo());

        $this->connection->insert('event', $event->toArray());
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
            UPDATE event
            SET comment = :comment
            WHERE id = :id
        SQL;

        $this->connection->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }

    private function addActor(Actor $actor): void
    {
        if ($this->readActorRepository->exist($actor->id())) {
            return;
        }

        $this->connection->insert('actor', $actor->toArray());
    }

    private function addRepo(Repo $repo): void
    {
        if ($this->readRepoRepository->exist($repo->id())) {
            return;
        }

        $this->connection->insert('repo', $repo->toArray());
    }
}
