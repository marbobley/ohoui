<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\Component\ComponentAwareQueryInterface;
use Solarium\Component\Result\Highlighting\Highlighting;
use Solarium\Exception\UnexpectedValueException;
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
            $highlighting = $result->getComponent(ComponentAwareQueryInterface::COMPONENT_HIGHLIGHTING);
        } catch (UnexpectedValueException) {
            $highlighting = null;
        }

        $rawHighlighting = [];
        if ($highlighting instanceof Highlighting) {
            foreach ($highlighting->getResults() as $docId => $highlight) {
                $rawHighlighting[(string) $docId] = $highlight->getFields();
            }
        }

        /** @var array<string, array<string, string[]>> $rawHighlighting */
        return new SolrResponse($documents, $result->getNumFound() ?? 0, $rawHighlighting);
    }
}
