<?php

declare(strict_types=1);

namespace App\Github\GhArchive\Client;

use App\Serializer\Denormalizer\EventDenormalizer;
use App\Serializer\Exception\UnsupportedEventTypeException;

final class GzOpenGhArchiveClient implements GhArchiveClientInterface
{
    public function __construct(
        private readonly string $ghArchiveBaseUri,
        private readonly EventDenormalizer $denormalizer,
    ) {
    }

    public function getEvents(\DateTimeInterface $dateTime): iterable
    {
        $handle = gzopen(sprintf('%s/%s.json.gz',
            $this->ghArchiveBaseUri,
            $dateTime->format('Y-m-d-H'),
        ), 'r');

        if ($handle === false) {
            throw new \RuntimeException();
        }

        while ($line = gzgets($handle)) {
            $data = json_decode($line, true);

            try {
                yield $this->denormalizer->denormalize($data);
            } catch (UnsupportedEventTypeException) {
            }
        }

        gzclose($handle);
    }
}
