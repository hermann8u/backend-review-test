<?php

declare(strict_types=1);

namespace App\Serializer\Exception;

final class UnsupportedEventTypeException extends \InvalidArgumentException
{
    private string $event;

    public function __construct(string $event, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Unsupported event type "%s"', $event), 0, $previous);

        $this->event = $event;
    }

    public function getEvent(): string
    {
        return $this->event;
    }
}
