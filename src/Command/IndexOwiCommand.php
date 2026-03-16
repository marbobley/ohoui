<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\ImportOwiUseCase;
use Exception;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(name: 'app:index-owi', description: 'Indexe les données de Owilix depuis le container Docker')]
class IndexOwiCommand extends Command
{
    /**
     * @throws LogicException
     */
    public function __construct(
        private readonly ImportOwiUseCase $importOwiUseCase,
    ) {
        parent::__construct();
    }

    #[Override]
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->addOption(
            name: 'limit',
            shortcut: 'l',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Nombre maximum de documents à indexer',
            default: '10',
        );

        $this->addOption(
            name: 'dataset',
            shortcut: 'd',
            mode: InputOption::VALUE_REQUIRED,
            description: 'ID du dataset OWI',
            default: 'a742176a-e940-11f0-8645-02a47ca5d9fd',
        );
    }

    #[Override]
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $dataset = (string) $input->getOption('dataset');

        $io->title(sprintf('Indexation des données OWI (Dataset: %s, Limit: %s)', $dataset, $limit));

        try {
            $io->info('Démarrage de l\'extraction et de l\'indexation...');

            $count = $this->importOwiUseCase->execute($dataset, $limit);

            $io->success(sprintf('%d documents ont été indexés avec succès.', $count));
        } catch (Exception $e) {
            $io->error(sprintf('Une erreur est survenue lors de l\'importation : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
