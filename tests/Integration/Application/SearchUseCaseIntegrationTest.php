<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\IndexUseCase;
use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function array_any;
use function uniqid;

final class SearchUseCaseIntegrationTest extends KernelTestCase
{
    public function testSearchUseCaseIntegration(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $indexUseCase = $container->get(IndexUseCase::class);
        $searchUseCase = $container->get(SearchUseCase::class);

        $id1 = 'test-search-1-' . uniqid();
        $id2 = 'test-search-2-' . uniqid();

        $doc1 = new Document(
            $id1,
            'Le Web Décentralisé en 2026',
            'https://decentralized-web.test',
            'L\'avenir du Web est décentralisé et ouvert à tous.',
            'fr',
            'decentralized-web.test',
        );

        $doc2 = new Document(
            $id2,
            'The Future of Decentralized Search',
            'https://future-search.test',
            'How decentralized indexes are changing search technology.',
            'en',
            'future-search.test',
        );

        $indexUseCase->execute($doc1);
        $indexUseCase->execute($doc2);

        // 1. Recherche simple par mot-clé dans le titre (par défaut)
        $results = $searchUseCase->execute('Décentralisé');
        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertSame('Le Web Décentralisé en 2026', $results[0]->getTitle());

        // 2. Recherche par champ spécifique (Solr syntax)
        $results = $searchUseCase->execute('language:en');
        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertContainsOnlyInstancesOf(Document::class, $results);

        // 3. Recherche avec opérateurs
        $results = $searchUseCase->execute('Future');
        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertContainsOnlyInstancesOf(Document::class, $results);

        // 4. Recherche par ID exact
        $results = $searchUseCase->execute('id:' . $id1);
        SearchUseCaseIntegrationTest::assertCount(1, $results);
        SearchUseCaseIntegrationTest::assertSame($id1, $results[0]->getId());

        // 5. Recherche avec caractères spéciaux
        $results = $searchUseCase->execute('title:Décentralisé');
        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertTrue(array_any($results, fn($d) => $d->getId() === $id1));
    }
}
