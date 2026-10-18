<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\User\Model\ApiToken;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class DefaultApiEndpointTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testRejectsMissingToken(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/api/default');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRejectsUnknownToken(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, '/api/default', server: ['HTTP_AUTHORIZATION' => 'Bearer not-a-real-token']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testReturnsTheCurrentUser(): void
    {
        $client = self::createClient();
        UserFactory::createOne([
            'email' => 'ivan@example.com',
            'apiTokenHash' => ApiToken::hash('secret-token'),
        ]);

        $client->request(Request::METHOD_GET, '/api/default', server: ['HTTP_AUTHORIZATION' => 'Bearer secret-token']);

        self::assertResponseIsSuccessful();
        $body = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('ivan@example.com', $body['email']);
    }
}
