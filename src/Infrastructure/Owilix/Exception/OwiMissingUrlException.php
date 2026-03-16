<?php

declare(strict_types=1);

namespace App\Infrastructure\Owilix\Exception;

use function sprintf;

final class OwiMissingUrlException extends OwilixException
{
    public static function inLine(string $line): self
    {
        return new self(sprintf('Missing URL in line: "%s"', $line));
    }
}
