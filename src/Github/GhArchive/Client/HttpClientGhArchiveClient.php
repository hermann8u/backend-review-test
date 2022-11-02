<?php

declare(strict_types=1);

namespace App\Github\GhArchive\Client;

use App\Serializer\Denormalizer\EventDenormalizer;
use App\Serializer\Exception\UnsupportedEventTypeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientGhArchiveClient implements GhArchiveClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $ghArchiveClient,
        private readonly EventDenormalizer $denormalizer,
    ) {
    }

    public function getEvents(\DateTimeInterface $dateTime): iterable
    {
        $response = $this->ghArchiveClient->request('GET', sprintf('/%s.json.gz', $dateTime->format('Y-m-d')), [
            'headers' => [
                'Accept-Encoding' => 'gzip',
            ],
        ]);

        foreach ($this->ghArchiveClient->stream($response) as $chunk) {
            $line = gzdecode($chunk->getContent());
            $data = json_decode($line, true);

            try {
                yield $this->denormalizer->denormalize($data);
            } catch (UnsupportedEventTypeException) {
            }
        }
    }
}
