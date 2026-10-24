<?php

declare(strict_types=1);

namespace App\Tests\Functional\Observability;

use App\Domain\User\Model\ApiToken;
use App\Tests\Factory\UserFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class TracingTest extends WebTestCase
{
    use Factories;
    use InteractsWithMessenger;
    use ResetDatabase;

    public function testRequestAndQueuedMessageShareOneTrace(): void
    {
        $client = self::createClient();
        UserFactory::createOne(['apiTokenHash' => ApiToken::hash('secret-token')]);

        $client->request('POST', '/api/telemetry/ping', server: ['HTTP_AUTHORIZATION' => 'Bearer secret-token']);
        self::assertResponseStatusCodeSame(202);

        $this->transport('async_low')->process(1); // what a worker would do

        $exporter = self::getContainer()->get(SpanExporterInterface::class);
        self::assertInstanceOf(InMemoryExporter::class, $exporter);

        $spans = [];
        foreach ($exporter->getSpans() as $span) {
            $spans[$span->getName()] = $span;
        }

        $request = $spans['POST api_telemetry_ping'] ?? self::fail('no request span');
        $message = $spans['process TelemetryPing'] ?? self::fail('no message span');

        self::assertSame($request->getTraceId(), $message->getTraceId());
        self::assertSame($request->getSpanId(), $message->getParentSpanId());
    }
}
