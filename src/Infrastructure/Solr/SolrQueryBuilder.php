<?php

declare(strict_types=1);

namespace App\Infrastructure\Solr;

use Override;
use Solarium\QueryType\Select\Query\Query as SelectQuery;

use function preg_match;
use function sprintf;
use function str_contains;
use function str_replace;

class SolrQueryBuilder implements SolrQueryBuilderInterface
{
    #[Override]
    public function build(SelectQuery $select, string $query, int $start, int $rows, array $filters): void
    {
        $helper = $select->getHelper();
        $this->configureQuery($select, $helper, $query);
        $this->configureFilters($select, $helper, $filters);
        $this->configureHighlighting($select);

        $select->setStart($start);
        $select->setRows($rows);
    }

    private function configureHighlighting(SelectQuery $select): void
    {
        $hl = $select->getHighlighting();
        $hl->setFields('content,title');
        $hl->setSimplePrefix('<em class="hl">');
        $hl->setSimplePostfix('</em>');
        $hl->setFragsize(200);
        $hl->setSnippets(1);
    }

    private function configureQuery(SelectQuery $select, \Solarium\Core\Query\Helper $helper, string $query): void
    {
        if (str_contains($query, ':')) {
            // Si l'utilisateur spécifie déjà un champ, on laisse passer mais on devrait idéalement parser.
            // Pour l'instant, on se contente de la recommandation de l'audit d'être prudent.
            $select->setQuery($query);

            return;
        }

        $hasSpecialChars = preg_match('/[*?~]/', $query) === 1;

        if ($hasSpecialChars) {
            // Pour les jokers, on échappe les caractères spéciaux SAUF les jokers eux-mêmes
            // Note: Solarium escapeTerm échappe tout. On doit faire un échappement partiel ou personnalisé.
            // On va utiliser escapeTerm et "dé-échapper" les jokers légitimes pour cette recherche.
            $escaped = $helper->escapeTerm($query);
            $queryWithJokers = str_replace(['\\*', '\\?', '\\~'], ['*', '?', '~'], $escaped);

            $query = sprintf('title:%1$s^2.0 OR content:%1$s', $queryWithJokers);
            $select->setQuery($query);

            return;
        }

        $escapedQuery = $helper->escapeTerm($query);
        // On cherche dans le titre avec un boost de 2.0 et dans le contenu par défaut
        $query = sprintf('title:"%1$s"^2.0 OR content:"%1$s"', $escapedQuery);

        $select->setQuery($query);
    }

    private function configureFilters(SelectQuery $select, \Solarium\Core\Query\Helper $helper, array $filters): void
    {
        /**
         * @var string $field
         * @var string $value
         */
        foreach ($filters as $field => $value) {
            $escapedValue = $helper->escapeTerm($value);
            $select->createFilterQuery($field)->setQuery(sprintf('%s:%s', $field, $escapedValue));
        }
    }
}
