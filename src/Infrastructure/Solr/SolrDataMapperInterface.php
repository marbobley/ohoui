<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Solarium\QueryType\Select\Result\Result;

interface SolrDataMapperInterface
{
    public function mapResponse(Result $result): SolrResponse;
}
