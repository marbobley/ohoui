<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Select\Result\Document;
use Solarium\QueryType\Select\Result\Result;

class SolrDataMapper implements SolrDataMapperInterface
{
    #[Override]
    public function mapResponse(Result $result): SolrResponse
    {
        /** @var list<array<string, mixed>> $documents */
        $documents = [];
        foreach ($result as $doc) {
            /** @var Document $doc */
            $documents[] = $doc->getFields();
        }

        try {
            /** @var mixed $highlighting */
            $highlighting = $result->getComponent(SelectQuery::COMPONENT_HIGHLIGHTING);
        } catch (\Solarium\Exception\UnexpectedValueException) {
            $highlighting = null;
        }

        $rawHighlighting = [];
        if ($highlighting instanceof \Solarium\Component\Result\Highlighting\Highlighting) {
            foreach ($highlighting->getResults() as $docId => $highlight) {
                $rawHighlighting[(string) $docId] = $highlight->getFields();
            }
        }

        /** @var array<string, array<string, string[]>> $rawHighlighting */
        return new SolrResponse($documents, $result->getNumFound() ?? 0, $rawHighlighting);
    }
}
