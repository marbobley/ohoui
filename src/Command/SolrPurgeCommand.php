<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\PurgeUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:solr:purge',
    description: 'Purge tous les documents de Solr',
)]
class SolrPurgeCommand extends Command
{
    public function __construct(
        private readonly PurgeUseCase $purgeUseCase,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$io->confirm('Êtes-vous sûr de vouloir purger tous les documents de Solr ?', false)) {
            $io->note('Opération annulée.');

            return Command::SUCCESS;
        }

        try {
            $this->purgeUseCase->execute();
            $io->success('Solr a été purgé avec succès.');
        } catch (\Exception $e) {
            $io->error(sprintf('Une erreur est survenue lors de la purge de Solr : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
