<?php

declare(strict_types=1);

namespace App\Service;

class SampleData
{
    /**
     * @return array<int, array{id: string, title: string, url: string, content: string, language: string, domain: string}>
     */
    public static function getSamples(): array
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
                'title' => 'PHP: Hypertext Preprocessor',
                'url' => 'https://php.net',
                'content' => 'PHP is a popular general-purpose scripting language that is especially suited to web development.',
                'language' => 'en',
                'domain' => 'php.net',
            ],
            [
                'id' => '5',
                'title' => 'MDN Web Docs - Documentation pour les développeurs Web',
                'url' => 'https://developer.mozilla.org',
                'content' => 'Les MDN Web Docs fournissent des informations sur les technologies Web ouvertes, notamment HTML, CSS et les API pour les sites Web et les applications Web progressives.',
                'language' => 'fr',
                'domain' => 'mozilla.org',
            ],
            [
                'id' => '6',
                'title' => 'Wikipedia - L\'encyclopédie libre',
                'url' => 'https://wikipedia.org',
                'content' => 'Wikipedia is a free online encyclopedia, created and edited by volunteers around the world and hosted by the Wikimedia Foundation.',
                'language' => 'en',
                'domain' => 'wikipedia.org',
            ],
            [
                'id' => '7',
                'title' => 'OWI Project - Open Web Index',
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
     * @return array{subjects: array<string, string[]>, adjectives: array<string, string[]>, verbs: array<string, string[]>}
     */
    public static function getRandomData(): array
    {
        return [
            'subjects' => [
                'fr' => [
                    'L\'écologie', 'La cuisine', 'Le sport', 'La musique', 'Le cinéma',
                    'L\'espace', 'La robotique', 'Le jardinage', 'Le voyage', 'La santé',
                ],
                'en' => [
                    'Ecology', 'Cooking', 'Sports', 'Music', 'Cinema',
                    'Space', 'Robotics', 'Gardening', 'Travel', 'Health',
                ],
                'de' => [
                    'Ökologie', 'Kochen', 'Sport', 'Musik', 'Kino',
                    'Weltraum', 'Robotik', 'Gartenarbeit', 'Reisen', 'Gesundheit',
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
}
