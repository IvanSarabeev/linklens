<?php

declare(strict_types=1);

namespace App\Application\Storage;

use App\Application\Storage\Port\UploadStreamInterface;
use App\Domain\Storage\Exception\InvalidUpload;
use App\Domain\Storage\Model\FilePurpose;
use App\Domain\Storage\Model\OriginalFilename;
use App\Domain\Storage\Model\StoredFile;
use App\Domain\Storage\Model\UploadPolicy;
use App\Domain\Storage\StoredFileRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class UploadFile
{
    private const string UPLOAD_PREFIX = 'UploadFile: ';

    public function __construct(
        private StoredFileRepositoryInterface $fileRepository,
        private UploadStreamInterface         $fileStorage,
        private ClockInterface                $clock,
        private LoggerInterface               $logger
    )
    { }

    public function __invoke(
        Uuid $ownerId,
        string $tmpPath,
        string $clientName,
        string $detectedMime
    ): UploadResult
    {
        $name = OriginalFilename::fromClient($clientName);
        $size = (int) filesize($tmpPath);

        try {
            UploadPolicy::reviewFormat($name, $detectedMime, $size);
        } catch (InvalidUpload $exception) {
            $this->logger->warning(
                self::UPLOAD_PREFIX . 'rejected',
                [
                    'user_id' => $ownerId->toRfc4122(),
                    'reason'  => $exception->uploadRejection->value
                ]
            );

            throw $exception;
        }

        $sha256 = (string) hash_file('sha256', $tmpPath);
        $existing = $this->fileRepository->findDuplicate($ownerId, $sha256, FilePurpose::IMPORT_SOURCE);

        if ($existing !== null) {
            $this->logger->info(self::UPLOAD_PREFIX . 'duplicate', ['file_id' => $existing->getUuid()->toRfc4122()]);

            return new UploadResult($existing, true);
        }

        $file = StoredFile::importSource(
            $ownerId,
            $name,
            $detectedMime,
            $size,
            $sha256,
            $this->clock->now()
        );

        $stream = fopen($tmpPath, 'rb') ?: throw new \RuntimeException('Cannot read the uploaded file.');

        try {
            $this->fileStorage->writeStream($file->getStorageKey(), $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->fileRepository->save($file);
        $this->logger->info(self::UPLOAD_PREFIX . 'uploaded', [
            'file_id'    => $file->getUuid()->toRfc4122(),
            'size_bytes' => $size
        ]);

        return new UploadResult($file, false);
    }


}
