<?php

declare(strict_types=1);

namespace App\Domain\Storage\Model;

enum FilePurpose: string
{
    case IMPORT_SOURCE = 'import_source';
    case EXPORT_PART   = 'export_part';
    case EXPORT_RESULT = 'export_result';
    case ERROR_REPORT  = 'error_report';
}
