<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

use function sprintf;
use function str_contains;

class SolrQueryBuilder implements SolrQueryBuilderInterface
{
    #[Override]
    public function build(SelectQuery $select, string $query, int $start, int $rows, array $filters): void
    {
        $this->configureQuery($select, $query);
        $this->configureFilters($select, $filters);

        $select->setStart($start);
        $select->setRows($rows);
    }

    private function configureQuery(SelectQuery $select, string $query): void
    {
        if (!str_contains($query, ':')) {
            $helper = $select->getHelper();
            $escapedQuery = $helper->escapeTerm($query);
            // On cherche dans le titre avec un boost de 2.0 et dans le contenu par défaut
            $query = sprintf('title:"%1$s"^2.0 OR content:"%1$s"', $escapedQuery);
        }

        $select->setQuery($query);
    }

    private function configureFilters(SelectQuery $select, array $filters): void
    {
        /**
         * @var string $field
         * @var string $value
         */
        foreach ($filters as $field => $value) {
            $select->createFilterQuery($field)->setQuery(sprintf('%s:%s', $field, $value));
        }
    }
}
