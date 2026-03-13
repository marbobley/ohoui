<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Solarium\QueryType\Select\Query\Query as SelectQuery;

interface SolrQueryBuilderInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    public function build(SelectQuery $select, string $query, int $start, int $rows, array $filters): void;
}
