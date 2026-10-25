<?php

declare(strict_types=1);

namespace App\Application\Storage\Port;

interface UploadStreamInterface
{
    /**
     * @param string $key
     * @param resource $stream
     * @return void
     */
    public function writeStream(string $key, $stream): void;

    /**
     * @param string $key
     * @return resource
     */
    public function readStream(string $key);
}
