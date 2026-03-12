<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class Facet
{
    /**
     * @param FacetValue[] $values
     */
    public function __construct(
        private string $name,
        private string $label,
        private array $values,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return FacetValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
