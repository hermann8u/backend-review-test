<?php

declare(strict_types=1);

namespace App\Github\GhArchive\Client;

use App\Entity\Event;

interface GhArchiveClientInterface
{
    /**
     * @return iterable<int, Event>
     */
    public function getEvents(\DateTimeInterface $dateTime): iterable;
}
