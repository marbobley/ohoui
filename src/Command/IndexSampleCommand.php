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
     * @return array<int, array{id: string, title: string, url: string, content: string, language: string, domain: string}>
     */
    private function getSamples(): array
    {
        return [
            [
                'id' => '1',
                'title' => 'OpenWebSearch.eu - Construire un index web ouvert',
                'url' => 'https://openwebsearch.eu',
                'content' => 'L\'initiative OpenWebSearch.eu vise à créer un index du Web européen ouvert et indépendant.',
                'language' => 'fr',
                'domain' => 'openwebsearch.eu',
            ],
            [
                'id' => '2',
                'title' => 'Le Monde - Actualités en France et dans le monde',
                'url' => 'https://lemonde.fr',
                'content' => 'Retrouvez toute l\'actualité nationale et internationale sur le premier site d\'information en France.',
                'language' => 'fr',
                'domain' => 'lemonde.fr',
            ],
            [
                'id' => '3',
                'title' => 'Symfony, le framework PHP pour les entreprises',
                'url' => 'https://symfony.com',
                'content' => 'Symfony est un ensemble de composants PHP réutilisables et un framework PHP pour les projets Web.',
                'language' => 'en',
                'domain' => 'symfony.com',
            ],
            [
                'id' => '4',
                'title' => 'La souveraineté numérique en Europe',
                'url' => 'https://europa.eu',
                'content' => 'L\'Europe travaille sur sa souveraineté numérique à travers divers projets comme l\'OWI.',
                'language' => 'fr',
                'domain' => 'europa.eu',
            ],
            [
                'id' => '5',
                'title' => 'PHP 8.4: New features and improvements',
                'url' => 'https://php.net/releases/8.4',
                'content' => 'PHP 8.4 is the latest version of the PHP language, bringing many new features like property hooks and asymmetric visibility.',
                'language' => 'en',
                'domain' => 'php.net',
            ],
            [
                'id' => '6',
                'title' => 'L\'intelligence artificielle au service de la recherche',
                'url' => 'https://ia-search.test',
                'content' => 'L\'IA transforme la manière dont nous recherchons l\'information sur le Web aujourd\'hui.',
                'language' => 'fr',
                'domain' => 'ia-search.test',
            ],
            [
                'id' => '7',
                'title' => 'The Open Web Index (OWI) Project',
                'url' => 'https://owi.test',
                'content' => 'The OWI project aims to provide a decentralized and open search index for the public interest.',
                'language' => 'en',
                'domain' => 'owi.test',
            ],
            [
                'id' => '8',
                'title' => 'Actualités technologiques en 2026',
                'url' => 'https://tech-news.test',
                'content' => 'Découvrez les dernières avancées technologiques de l\'année 2026, incluant le Web décentralisé.',
                'language' => 'fr',
                'domain' => 'tech-news.test',
            ],
        ];
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
