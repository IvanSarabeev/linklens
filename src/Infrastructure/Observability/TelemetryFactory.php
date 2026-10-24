<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter as InMemoryLogExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter as InMemorySpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class TelemetryFactory
{
    public function __construct(
        #[Autowire(env: 'TELEMETRY_EXPORTER')]
        private string $exporter,
        #[Autowire(env: 'OTEL_EXPORTER_OTLP_ENDPOINT')]
        private string $endpoint,
        #[Autowire(env: 'SERVICE_NAME')]
        private string $serviceName
    )
    { }

    public function spanExporter(): SpanExporterInterface
    {
        return $this->inMemory()
            ? new InMemorySpanExporter()
            : new SpanExporter(new OtlpHttpTransportFactory()->create($this->url('/v1/traces'), 'application/x-protobuf'));
    }

    public function logExporter(): LogRecordExporterInterface
    {
        return $this->inMemory()
            ? new InMemoryLogExporter()
            : new LogsExporter(new OtlpHttpTransportFactory()->create($this->url('/v1/logs'), 'application/x-protobuf'));
    }

    public function tracerProvider(SpanExporterInterface $exporter): TracerProviderInterface
    {
        return TracerProvider::builder()
            ->setResource($this->resource())
            ->addSpanProcessor(
                $this->inMemory()
                    ? new SimpleSpanProcessor($exporter)
                    : new BatchSpanProcessor($exporter, Clock::getDefault(), autoFlush: false))
            ->build();
    }

    public function loggerProvider(LogRecordExporterInterface $exporter): LoggerProviderInterface
    {
        return LoggerProvider::builder()
            ->setResource($this->resource())
            ->addLogRecordProcessor(
                $this->inMemory()
                    ? new SimpleLogRecordProcessor($exporter)
                    : new BatchLogRecordProcessor($exporter, Clock::getDefault(), autoFlush: false)
            )->build();
    }

    private function inMemory(): bool
    {
        return 'memory' === $this->exporter;
    }

    private function url(string $path): string
    {
        return rtrim($this->endpoint, '/') . $path;
    }

    private function resource(): ResourceInfo
    {
        return ResourceInfoFactory::emptyResource()->merge(ResourceInfo::create(
            Attributes::create([
                'service.name' => $this->serviceName,
                'service.instance.id' => (gethostname() ?: 'host') . '-' . getmypid(),
                'linklens.process' => PHP_SAPI === 'cli' ? 'cli' : 'web'
            ])
        ));
    }
}
