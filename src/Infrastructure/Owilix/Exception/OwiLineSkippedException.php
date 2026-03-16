<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix\Exception;

use function sprintf;

final class OwiLineSkippedException extends OwilixException
{
    public static function because(string $line): self
    {
        return new self(sprintf('Line skipped: "%s"', $line));
    }
}
