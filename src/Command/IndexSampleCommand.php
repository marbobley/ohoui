<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\SolrClientService;
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
     * @param SolrClientService $solrClient
     * @throws LogicException
     */
    public function __construct(
        private readonly SolrClientService $solrClient,
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
        ];

        foreach ($samples as $sample) {
            $this->solrClient->indexDocument($sample);
            $io->note(sprintf('Indexé : %s', $sample['title']));
        }

        $io->success('Échantillon de données indexé avec succès.');

        return Command::SUCCESS;
    }
}
