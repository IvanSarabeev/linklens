<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistance\Doctrine;

use App\Domain\User\Model\User;
use App\Domain\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    { }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Find User by unique Uuid v7
     *
     * @param Uuid $uuid
     * @return User|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findByUuid(Uuid $uuid): ?User
    {
        return $this->entityManager->find(User::class, $uuid);
    }

    public function findByApiTokenHash(string $hash): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['apiTokenHash' => $hash]);
    }

}
