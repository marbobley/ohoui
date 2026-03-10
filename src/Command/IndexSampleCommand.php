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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_rand;
use function count;
use function is_numeric;
use function mb_strtolower;
use function preg_replace;
use function sprintf;
use function uniqid;

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

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var mixed $countValue */
        $countValue = $input->getOption('count');
        $count = is_numeric($countValue) ? (int) $countValue : 0;

        $samples = $this->getSamples();
        foreach ($samples as $sample) {
            $this->indexDocument($sample);
            $io->note(sprintf('Indexé : %s', $sample['title']));
        }

        if ($count > 0) {
            $io->section(sprintf('Génération de %d documents aléatoires...', $count));
            for ($i = 0; $i < $count; $i++) {
                $generated = $this->generateRandomDocument();
                $this->indexDocument($generated);
                if ((($i + 1) % 10) === 0 || $count <= 10) {
                    $io->note(sprintf('Généré (%d/%d) : %s', $i + 1, $count, $generated['title']));
                }
            }
        }

        $io->success(sprintf('%d documents indexés avec succès.', count($samples) + $count));

        return Command::SUCCESS;
    }

    /**
     * @param array{id: string, title: string, url: string, content: string, language: string, domain: string} $data
     */
    private function indexDocument(array $data): void
    {
        $document = new Document(
            $data['id'],
            $data['title'],
            $data['url'],
            $data['content'],
            $data['language'],
            $data['domain'],
        );
        $this->indexUseCase->execute($document);
    }

    /**
     * @return array{subjects: array<string, string[]>, adjectives: array<string, string[]>, verbs: array<string, string[]>}
     */
    private function getRandomData(): array
    {
        return [
            'subjects' => [
                'fr' => [
                    'L\'écologie',
                    'La cuisine',
                    'Le sport',
                    'La musique',
                    'Le cinéma',
                    'L\'espace',
                    'La robotique',
                    'Le jardinage',
                    'Le voyage',
                    'La santé',
                ],
                'en' => [
                    'Ecology',
                    'Cooking',
                    'Sports',
                    'Music',
                    'Cinema',
                    'Space',
                    'Robotics',
                    'Gardening',
                    'Travel',
                    'Health',
                ],
                'de' => [
                    'Ökologie',
                    'Kochen',
                    'Sport',
                    'Musik',
                    'Kino',
                    'Weltraum',
                    'Robotik',
                    'Gartenarbeit',
                    'Reisen',
                    'Gesundheit',
                ],
            ],
            'adjectives' => [
                'fr' => ['moderne', 'durable', 'futuriste', 'traditionnel', 'passionnant', 'essentiel'],
                'en' => ['modern', 'sustainable', 'futuristic', 'traditional', 'exciting', 'essential'],
                'de' => ['modern', 'nachhaltig', 'futuristisch', 'traditionell', 'spannend', 'wesentlich'],
            ],
            'verbs' => [
                'fr' => ['découvrir', 'comprendre', 'explorer', 'analyser', 'partager', 'améliorer'],
                'en' => ['discover', 'understand', 'explore', 'analyze', 'share', 'improve'],
                'de' => ['entdecken', 'verstehen', 'erkunden', 'analysieren', 'teilen', 'verbessern'],
            ],
        ];
    }

    /**
     * @return array{id: string, title: string, url: string, content: string, language: string, domain: string}
     */
    private function generateRandomDocument(): array
    {
        $data = $this->getRandomData();
        $langs = ['fr', 'en', 'de'];
        $lang = $langs[array_rand($langs)];

        $subject = $data['subjects'][$lang][array_rand($data['subjects'][$lang])];
        $adj = $data['adjectives'][$lang][array_rand($data['adjectives'][$lang])];
        $verb = $data['verbs'][$lang][array_rand($data['verbs'][$lang])];

        $title = match ($lang) {
            'fr' => sprintf('%s %s : pourquoi il faut %s le futur', $subject, $adj, $verb),
            'en' => sprintf('%s %s: why we need to %s the future', $adj, $subject, $verb),
            default => sprintf('%s %s: warum wir die Zukunft %s müssen', $adj, $subject, $verb),
        };

        $id = uniqid(prefix: 'rand_', more_entropy: true);
        $subjectSlug = (string) preg_replace(pattern: '/[^a-z0-9]+/i', replacement: '-', subject: $subject);
        $slug = mb_strtolower(string: $subjectSlug);
        $domain = $slug . '.example.com';
        $url = 'https://' . $domain . '/' . $id;
        $content = sprintf(
            'Contenu généré aléatoirement à propos de %s (%s). Cet article explore comment %s peut être %s pour tout le monde.',
            $subject,
            $lang,
            $subject,
            $adj,
        );

        return [
            'id' => $id,
            'title' => $title,
            'url' => $url,
            'content' => $content,
            'language' => $lang,
            'domain' => $domain,
        ];
    }
}
