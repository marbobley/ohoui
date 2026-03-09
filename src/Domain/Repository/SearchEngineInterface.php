<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Document;

interface SearchEngineInterface
{
    public function index(Document $document): void;

    /**
     * @return Document[]
     */
    public function search(string $query): array;
}
