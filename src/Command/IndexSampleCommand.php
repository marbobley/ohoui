<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Service\IndexUseCase;
use App\Domain\Model\Document;
use LogicException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(name: 'app:index-sample', description: 'Indexe un échantillon de données simulant l\'OWI dans Solr')]
class IndexSampleCommand extends Command
{
    /**
     * @param IndexUseCase $indexUseCase
     * @throws LogicException
     */
    public function __construct(
        private readonly IndexUseCase $indexUseCase,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $samples = [
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

        foreach ($samples as $sample) {
            $document = new Document(
                $sample['id'],
                $sample['title'],
                $sample['url'],
                $sample['content'],
                $sample['language'],
                $sample['domain'],
            );
            $this->indexUseCase->execute($document);
            $io->note(sprintf('Indexé : %s', $sample['title']));
        }

        $io->success('Échantillon de données indexé avec succès.');

        return Command::SUCCESS;
    }
}
