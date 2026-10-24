<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class TraceContextStamp implements StampInterface
{
    private const string X_HEADER = 'traceparent';

    public function __construct(public array $carrier)
    { }

    /** traceparent = 00-<trace id>-<span id>-<flags> */
    public function traceId(): ?string
    {
        return explode('-', $this->carrier[self::X_HEADER] ?? '')[1] ?? null;
    }
}
