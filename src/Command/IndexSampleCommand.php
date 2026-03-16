<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\IndexUseCase;
use App\Domain\Model\Document;
use App\Service\SampleDataProvider;
use LogicException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function is_numeric;
use function sprintf;

#[AsCommand(name: 'app:index-sample', description: 'Indexe un échantillon de données simulant l\'OWI dans Solr')]
class IndexSampleCommand extends Command
{
    /**
     * @param IndexUseCase $indexUseCase
     * @param SampleDataProvider $sampleDataProvider
     * @throws LogicException
     */
    public function __construct(
        private readonly IndexUseCase $indexUseCase,
        private readonly SampleDataProvider $sampleDataProvider,
    ) {
        parent::__construct();
    }

    /**
     * @param int $count
     * @param SymfonyStyle $io
     * @return void
     */
    private function populate(int $count, SymfonyStyle $io): void
    {
        for ($i = 0; $i < $count; $i++) {
            $generated = $this->sampleDataProvider->generateRandomDocument();
            $this->indexDocument($generated);
            $this->printMessage($i, $count, $io, $generated['title']);
        }
    }

    /**
     * @param int $i
     * @param int $count
     * @param SymfonyStyle $io
     * @param string $title
     * @return void
     */
    private function printMessage(int $i, int $count, SymfonyStyle $io, string $title): void
    {
        if ((($i + 1) % 10) === 0 || $count <= 10) {
            $io->note(sprintf('Généré (%d/%d) : %s', $i + 1, $count, (string) $title));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function configure(): void
    {
        $this->addOption(
            'count',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Nombre de documents additionnels aléatoires à générer',
            '0',
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var mixed $countValue */
        $countValue = $input->getOption('count');
        $count = is_numeric($countValue) ? (int) $countValue : 0;

        $samples = $this->sampleDataProvider->getSamples();
        foreach ($samples as $sample) {
            $this->indexDocument($sample);
            $io->note(sprintf('Indexé : %s', $sample['title']));
        }

        if ($count > 0) {
            $io->section(sprintf('Génération de %d documents aléatoires...', $count));
            $this->populate($count, $io);
        }

        $io->success(sprintf('%d documents indexés avec succès.', count($samples) + $count));

        return Command::SUCCESS;
    }

    /**
     * @param array{id: string, title: string, url: string, content: string, language: string, domain: string} $data
     */
    private function indexDocument(array $data): void
    {
        $this->indexUseCase->execute(
            new Document(
                $data['id'],
                $data['title'],
                $data['url'],
                $data['content'],
                $data['language'],
                $data['domain'],
            ),
        );
    }
}
