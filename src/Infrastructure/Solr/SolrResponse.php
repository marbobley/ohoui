<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

readonly class SolrResponse
{
    /**
     * @param list<array<array-key, mixed>> $documents
     */
    public function __construct(
        public array $documents,
        public int $numFound,
    ) {}
}
