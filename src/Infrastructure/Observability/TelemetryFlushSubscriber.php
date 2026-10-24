<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;

final readonly class TelemetryFlushSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TracerProviderInterface $tracers,
        private LoggerProviderInterface $loggers,
    )
    { }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['flush', -1024],
            ConsoleEvents::TERMINATE => ['flush', -1024],
            WorkerRunningEvent::class => 'onWorkerRunning',
            WorkerStoppedEvent::class => 'flush',
        ];
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($event->isWorkerIdle()) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->tracers->forceFlush();
        $this->loggers->forceFlush();
    }
}
