<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix\Exception;

use function sprintf;

final class OwiInvalidJsonException extends OwilixException
{
    public static function forLine(string $line): self
    {
        return new self(sprintf('Invalid JSON format for line: "%s"', $line));
    }
}
