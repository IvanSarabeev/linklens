<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistance\Doctrine;

use App\Domain\Storage\Model\FilePurpose;
use App\Domain\Storage\Model\StoredFile;
use App\Domain\Storage\StoredFileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use SortDirection;
use Symfony\Component\Uid\Uuid;

final class DoctrineStoredFileRepository implements StoredFileRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(StoredFile $file): void
    {
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }

    /**
     * @param Uuid $uuid
     * @return StoredFile|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findById(Uuid $uuid): ?StoredFile
    {
        return $this->entityManager->find(StoredFile::class, $uuid);
    }

    /**
     * Find duplicated owner file
     *
     * @param Uuid $ownerId
     * @param string $sha256
     * @param FilePurpose $purpose
     * @return StoredFile|null
     */
    public function findDuplicate(Uuid $ownerId, string $sha256, FilePurpose $purpose): ?StoredFile
    {
        return $this->entityManager->getRepository(StoredFile::class)->findOneBy(['ownerId' => $ownerId, 'sha256' => $sha256, 'purpose' => $purpose]);
    }

    /**
     * Find file by ownerId
     *
     * @param Uuid $ownerId
     * @param int $limit
     * @return array
     */
    public function findByOwner(Uuid $ownerId, int $limit = 50): array
    {
        return array_values(
            $this->entityManager->getRepository(StoredFile::class)
                ->findBy(
                    ['ownerId' => $ownerId],
                    ['createdAt' => SortDirection::Descending->name],
                    $limit
                )
        );
    }

}
