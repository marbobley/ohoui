<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\SearchCriteria;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function explode;
use function sprintf;
use function str_contains;

#[AsCommand(name: 'app:search', description: 'Recherche des documents dans Solr')]
final class SearchCommand extends Command
{
    /**
     * @throws LogicException
     */
    public function __construct(
        private readonly SearchUseCase $searchUseCase,
    ) {
        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'Le terme de recherche')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre de résultats maximum', 10)
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Décalage des résultats', 0)
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Filtres de recherche (ex: domain:example.com)',
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = (string) $input->getArgument('query');
        $limit = (int) $input->getOption('limit');
        $offset = (int) $input->getOption('offset');
        /** @var string[] $rawFilters */
        $rawFilters = $input->getOption('filter');

        $filters = [];
        foreach ($rawFilters as $filter) {
            if (!str_contains($filter, ':')) {
                continue;
            }

            [$field, $value] = explode(':', $filter, limit: 2);
            $filters[$field] = $value;
        }

        $io->title(sprintf('Recherche pour : "%s"', $query));

        if ([] !== $filters) {
            $io->section('Filtres actifs :');
            foreach ($filters as $field => $value) {
                $io->text(sprintf('- %s: %s', $field, $value));
            }
        }

        try {
            $page = ($offset / $limit) + 1;
            $criteria = new SearchCriteria($query, (int) $page, $limit, $filters);
            $categorizedResult = $this->searchUseCase->execute($criteria);

            if (0 === $categorizedResult->getTotalCount()) {
                $io->warning('Aucun résultat trouvé.');

                return Command::SUCCESS;
            }

            // Pour la CLI, on n'affiche que les résultats pertinents
            $documents = $categorizedResult->getRelevant();

            $io->success(sprintf(
                'Trouvé %d document(s) (total: %d)',
                count($documents),
                $categorizedResult->getTotalCount(),
            ));

            $rows = [];
            foreach ($documents as $document) {
                $rows[] = [
                    $document->getTitle(),
                    $document->getUrl(),
                    $document->getDomain(),
                ];
            }

            $io->table(['Titre', 'URL', 'Domaine'], $rows);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Une erreur est survenue lors de la recherche : %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
