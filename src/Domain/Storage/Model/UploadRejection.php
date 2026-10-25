<?php

declare(strict_types=1);

namespace App\Domain\Storage\Model;

enum UploadRejection: string
{
    case EMPTY                 = 'empty';
    case TOO_LARGE             = 'too_large';
    case UNSUPPORTED_EXTENSION = 'unsupported_extension';
    case CONTENT_MISMATCH      = 'content_mismatch';
    case UPLOAD_FAILED         = 'upload_failed';

    public function message(): string
    {
        return match ($this) {
            self::EMPTY => 'The file is empty.',
            self::TOO_LARGE => 'The file is larger than 5 MB.',
            self::UNSUPPORTED_EXTENSION => 'Only .csv and .xlsx files are accepted.',
            self::CONTENT_MISMATCH => 'The file content does not match its extension.',
            self::UPLOAD_FAILED => 'The upload did not complete.'
        };
    }
}
