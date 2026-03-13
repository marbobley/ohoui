<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\SolrHealthUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:solr:health',
    description: 'Affiche l\'état de santé de l\'instance Solr',
)]
class SolrHealthCommand extends Command
{
    public function __construct(
        private readonly SolrHealthUseCase $healthUseCase,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Diagnostic de santé Solr');

        try {
            $status = $this->healthUseCase->execute();

            $io->success('Connexion à Solr établie.');

            $rows = [];
            foreach ($status as $key => $value) {
                $rows[] = [$key, $value];
            }

            $io->table(['Indicateur', 'Valeur'], $rows);

        } catch (\Exception $e) {
            $io->error(sprintf('Impossible de contacter Solr : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
