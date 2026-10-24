<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class HttpTracingSubscriber implements EventSubscriberInterface
{
    public function __construct(private TracerInterface $tracer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 16],
            KernelEvents::EXCEPTION => ['onException', 0],
            KernelEvents::RESPONSE => ['onResponse', -1024],
            // end the span before the flush (-1024)
            KernelEvents::TERMINATE => ['onTerminate', 1024],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $span = $this->tracer->spanBuilder(
            sprintf(
                '%s %s',
                $request->getMethod(),
                is_string($route) ? $route : $request->getPathInfo()
            ))
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setAttributes([
                'http.request.method' => $request->getMethod(),
                'url.path' => $request->getPathInfo(),
            ])
            ->startSpan();

        $request->attributes->set('_otel_span', $span);
        $request->attributes->set('_otel_scope', $span->activate());
    }

    public function onException(ExceptionEvent $event): void
    {
        $span = $event->getRequest()->attributes->get('_otel_span');

        if ($span instanceof SpanInterface) {
            $span->recordException($event->getThrowable());
            $span->setStatus(StatusCode::STATUS_ERROR, $event->getThrowable()->getMessage());
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        $span = $event->getRequest()->attributes->get('_otel_span');

        if (!$event->isMainRequest() || !$span instanceof SpanInterface) {
            return;
        }

        $status = $event->getResponse()->getStatusCode();
        $span->setAttribute('http.response.status_code', $status);

        if ($status >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $span->setStatus(StatusCode::STATUS_ERROR);
        }
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $scope = $event->getRequest()->attributes->get('_otel_scope');
        $span = $event->getRequest()->attributes->get('_otel_span');

        if ($scope instanceof ScopeInterface) {
            $scope->detach();
        }

        if ($span instanceof SpanInterface) {
            $span->end();
        }
    }
}
