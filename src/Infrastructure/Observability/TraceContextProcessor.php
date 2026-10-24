<?php

declare(strict_types=1);

namespace App\Infrastructure\Observability;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use OpenTelemetry\API\Trace\Span;

#[AsMonologProcessor]
final class TraceContextProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = Span::getCurrent()->getContext();

        if ($context->isValid()) {
            $record->extra['trace_id'] = $context->getTraceId();
            $record->extra['span_id']  = $context->getSpanId();
        }

        return $record;
    }
}
