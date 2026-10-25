<?php

declare(strict_types=1);

namespace App\Domain\Storage\Model;

use App\Domain\Storage\Exception\InvalidUpload;

final class UploadPolicy
{
    // Max bytes 5 MB
    public const int MAX_BYTES = 5 * 1024 * 1024;

    private const array ALLOWED_EXTENSIONS = [
        'csv' => ['text/csv', 'text/plain', 'application/csv'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    ];

    public static function reviewFormat(OriginalFilename $name, string $detectedMime, int $sizeBytes): void
    {
        if ($sizeBytes === 0) {
            throw InvalidUpload::because(UploadRejection::EMPTY);
        }

        if ($sizeBytes > self::MAX_BYTES) {
            throw InvalidUpload::because(UploadRejection::TOO_LARGE);
        }

        $allowed = self::ALLOWED_EXTENSIONS[$name->extension()] ?? null;

        if ($allowed === null) {
            throw InvalidUpload::because(UploadRejection::UNSUPPORTED_EXTENSION);
        }

        if (in_array($detectedMime, $allowed, true)) {
            throw InvalidUpload::because(UploadRejection::CONTENT_MISMATCH);
        }
    }
}
