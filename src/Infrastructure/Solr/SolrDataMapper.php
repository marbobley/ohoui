<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\Component\Result\Facet\Field as SolariumFacetField;
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

        $facets = [];
        $facetSet = $result->getFacetSet();
        if ($facetSet !== null) {
            foreach ($facetSet as $facetName => $facet) {
                if (!$facet instanceof \Solarium\Component\Result\Facet\Field) {
                    continue;
                }

                $facetValues = [];
                foreach ($facet as $value => $count) {
                    $facetValues[(string) $value] = (int) $count;
                }
                $facets[(string) $facetName] = $facetValues;
            }
        }

        return new SolrResponse($documents, $result->getNumFound() ?? 0, $facets);
    }
}
