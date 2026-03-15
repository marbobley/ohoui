<?php

declare(strict_types=1);

namespace App\Domain\Model;

use Webmozart\Assert\Assert;

final readonly class SearchCriteria
{
    /**
     * @param array<string, string> $filters
     */
    public function __construct(
        private string $query,
        private int $page = 1,
        private int $limit = 10,
        private array $filters = [],
    ) {
        Assert::minLength($this->query, 1, 'La requête ne peut pas être vide.');
        Assert::greaterThanEq($this->page, 1, 'La page doit être supérieure ou égale à 1.');
        Assert::range($this->limit, 1, 100, 'La limite doit être comprise entre 1 et 100.');
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    /**
     * @return array<string, string>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
