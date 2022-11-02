<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Event;
use App\Entity\EventType;
use App\Serializer\Exception\UnsupportedEventTypeException;

final class EventDenormalizer
{
    /**
     * @throws UnsupportedEventTypeException
     */
    public function denormalize(array $data): Event
    {
        $type = EventType::GH_ARCHIVE_MAP[$data['type']] ?? null;

        if ($type === null) {
            throw new UnsupportedEventTypeException($data['type']);
        }

        $data['type'] = $type;

        return Event::fromArray($data);
    }
}
