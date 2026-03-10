<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class FacetValue
{
    public function __construct(
        private string $value,
        private int $count,
    ) {}

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
