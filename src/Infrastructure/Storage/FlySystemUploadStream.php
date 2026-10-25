<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\Storage\Port\UploadStreamInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FlySystemUploadStream implements UploadStreamInterface
{
    public function __construct(
        #[Autowire(service: 'imports.storage')]
        private FilesystemOperator $filesystemOperator,
    )
    { }

    /**
     * @param string $key
     * @param $stream
     * @return void
     * @throws FilesystemException
     */
    public function writeStream(string $key, $stream): void
    {
        $this->filesystemOperator->writeStream($key, $stream);
    }

    /**
     * @param string $key
     * @return resource
     * @throws FilesystemException
     */
    public function readStream(string $key)
    {
        return $this->filesystemOperator->readStream($key);
    }
}
