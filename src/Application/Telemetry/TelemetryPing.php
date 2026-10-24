<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async_low')]
final readonly class TelemetryPing
{
    public function __construct(public string $requestBody)
    { }
}
