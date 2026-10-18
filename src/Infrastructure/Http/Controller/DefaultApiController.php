<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Infrastructure\Http\Security\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class DefaultApiController extends AbstractController
{
    #[Route('/api/default', name: 'api_default', methods: ['GET'])]
    public function __invoke(#[CurrentUser] SecurityUser $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}
