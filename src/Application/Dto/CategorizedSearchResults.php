<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Domain\Model\Document;

final readonly class CategorizedSearchResults
{
    /**
     * @param Document[] $relevant
     * @param Document[] $recent
     * @param Document[] $random
     */
    public function __construct(
        private array $relevant,
        private array $recent,
        private array $random,
        private int $totalCount,
    ) {}

    /** @return Document[] */
    public function getRelevant(): array
    {
        return $this->relevant;
    }

    /** @return Document[] */
    public function getRecent(): array
    {
        return $this->recent;
    }

    /** @return Document[] */
    public function getRandom(): array
    {
        return $this->random;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
