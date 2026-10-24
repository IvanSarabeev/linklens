<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class TelemetryPingHandler
{
    public function __construct(private LoggerInterface $logger)
    { }

    public function __invoke(TelemetryPing $telemetryPing): void
    {
        $this->logger->info('telemetry.pong', ['requested_by' => $telemetryPing->requestBody]);
    }


}
