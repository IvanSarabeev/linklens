<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Opentelemetry\Proto\Trace\V1\Span\SpanKind;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Throwable;

final readonly class TracingMiddleware implements MiddlewareInterface
{
    public function __construct(private TracerInterface $tracer)
    { }

    /**
     * @param Envelope $envelope
     * @param StackInterface $stack
     * @return Envelope
     * @throws ExceptionInterface
     * @throws Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null == $envelope->last(ReceivedStamp::class)) {
            return $stack->next()->handle($this->withTraceContext($envelope), $stack);
        }

        $carrier = $envelope->last(TraceContextStamp::class)?->carrier ?? [];
        $span = $this->tracer->spanBuilder('process ' . new \ReflectionClass($envelope->getMessage())->getShortName())
            ->setParent(TraceContextPropagator::getInstance()->extract($carrier))
            ->setSpanKind(SpanKind::SPAN_KIND_CONSUMER)
            ->setAttribute('messaging.message.type', $envelope->getMessage()::class)
            ->startSpan();

        $scope = $span->activate();

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (Throwable $exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

            throw $exception;
        } finally {
            $scope->detach();
            $span->end();
    }
    }

    private function withTraceContext(Envelope $envelope): Envelope
    {
        if (null !== $envelope->last(TraceContextStamp::class)) {
            // keep the original trace on re-dispatch
            return $envelope;
        }

        $carrier = [];
        TraceContextPropagator::getInstance()->inject($carrier);

        return [] === $carrier
            ? $envelope
            : $envelope->with(new TraceContextStamp($carrier));
    }
}
