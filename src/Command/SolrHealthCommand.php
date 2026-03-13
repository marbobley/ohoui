<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\SolrHealthUseCaseInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(name: 'app:solr:health', description: 'Affiche l\'état de santé de l\'instance Solr')]
class SolrHealthCommand extends Command
{
    /**
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(
        private readonly SolrHealthUseCaseInterface $healthUseCase,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Diagnostic de santé Solr');

        try {
            $status = $this->healthUseCase->execute();

            $io->success('Connexion à Solr établie.');

            $rows = [];
            /** @var mixed $value */
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
