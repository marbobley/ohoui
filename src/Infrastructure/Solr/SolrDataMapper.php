<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\QueryType\Select\Result\Result;

class SolrDataMapper implements SolrDataMapperInterface
{
    #[Override]
    public function mapResponse(\Solarium\QueryType\Select\Result\Result $result): SolrResponse
    {
        /** @var list<array<string, mixed>> $documents */
        $documents = [];
        foreach ($result as $doc) {
            /** @var \Solarium\QueryType\Select\Result\Document $doc */
            $documents[] = $doc->getFields();
        }

        return new SolrResponse($documents, $result->getNumFound() ?? 0);
    }
}
