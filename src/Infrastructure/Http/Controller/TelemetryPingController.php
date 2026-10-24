<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Telemetry\TelemetryPing;
use App\Infrastructure\Http\Security\SecurityUser;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class TelemetryPingController extends AbstractController
{
    /**
     * @param SecurityUser $user
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/api/telemetry/ping', name: 'api_telemetry_ping', methods: ['POST'])]
    public function __invoke(
        #[CurrentUser] SecurityUser $user,
        MessageBusInterface         $bus,
        LoggerInterface             $logger
    ): JsonResponse
    {
        $logger->info('telemetry.ping', ['user_id' => $user->getUserIdentifier()]);
        $bus->dispatch(new TelemetryPing($user->getUserIdentifier()));

        return $this->json(['status' => 'queued'], Response::HTTP_ACCEPTED);
    }

}
