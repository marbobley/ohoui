<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

use function array_rand;
use function mb_strtolower;
use function mt_rand;
use function preg_replace;
use function sprintf;
use function uniqid;

class SampleDataProvider
{
    /**
     * @return array<int, array{id: string, title: string, url: string, content: string, language: string, domain: string, indexed_at: DateTimeImmutable}>
     */
    public function getSamples(): array
    {
        $samples = SampleData::getSamples();
        $results = [];
        foreach ($samples as $sample) {
            $sample['indexed_at'] = new DateTimeImmutable('-' . mt_rand(min: 0, max: 30) . ' days');
            $results[] = $sample;
        }

        return $results;
    }

    /**
     * @return array{id: string, title: string, url: string, content: string, language: string, domain: string, indexed_at: DateTimeImmutable}
     */
    public function generateRandomDocument(): array
    {
        $data = SampleData::getRandomData();
        $lang = ['fr', 'en', 'de'][array_rand(['fr', 'en', 'de'])];

        $subject = $data['subjects'][$lang][array_rand($data['subjects'][$lang])];
        $adj = $data['adjectives'][$lang][array_rand($data['adjectives'][$lang])];
        $verb = $data['verbs'][$lang][array_rand($data['verbs'][$lang])];

        $title = match ($lang) {
            'fr' => sprintf('%s %s : pourquoi il faut %s le futur', $subject, $adj, $verb),
            'en' => sprintf('%s %s: why we need to %s the future', $adj, $subject, $verb),
            default => sprintf('%s %s: warum wir die Zukunft %s müssen', $adj, $subject, $verb),
        };

        $id = uniqid(prefix: 'rand_', more_entropy: true);
        $slug = mb_strtolower((string) preg_replace('/[^a-z0-9]+/i', replacement: '-', subject: $subject));
        $domain = $slug . '.example.com';

        return [
            'id' => $id,
            'title' => $title,
            'url' => 'https://' . $domain . '/' . $id,
            'content' => sprintf(
                'Contenu généré aléatoirement à propos de %s (%s). Cet article explore comment %s peut être %s pour tout le monde.',
                $subject,
                $lang,
                $subject,
                $adj,
            ),
            'language' => $lang,
            'domain' => $domain,
            'indexed_at' => new DateTimeImmutable('-' . mt_rand(min: 0, max: 365) . ' days'),
        ];
    }
}
