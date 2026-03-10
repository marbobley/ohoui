<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\IndexUseCase;
use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function str_repeat;
use function uniqid;

final class IndexUseCaseIntegrationTest extends KernelTestCase
{
    public function testIndexUseCaseIntegration(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var IndexUseCase $indexUseCase */
        $indexUseCase = $container->get(IndexUseCase::class);

        // Cas 1 : Document standard
        $id1 = 'test-id-' . uniqid();
        $doc1 = new Document(
            $id1,
            'Test Indexation Réelle',
            'https://test-integration.com',
            'Contenu de test pour intégration réelle',
            'fr',
            'test-integration.com',
        );

        // Cas 2 : Document avec caractères spéciaux et contenu long
        $id2 = 'test-special-' . uniqid();
        $longContent = str_repeat('Ceci est un contenu très long pour tester la capacité de stockage de Solr. ', 100);
        $doc2 = new Document(
            $id2,
            'Titre avec "guillemets" & spéciaux : éàïô €',
            'https://test-special.com/path?query=1',
            $longContent,
            'en',
            'test-special.com',
        );

        // Cas 3 : Document avec contenu minimal (vide)
        $id3 = 'test-empty-' . uniqid();
        $doc3 = new Document($id3, 'Titre Seul', 'https://empty.test', '', 'fr', 'empty.test');

        // Exécution
        $indexUseCase->execute($doc1);
        $indexUseCase->execute($doc2);
        $indexUseCase->execute($doc3);

        // Vérifications via SearchUseCase
        $searchUseCase = $container->get(SearchUseCase::class);

        // Vérification Doc 1
        $results1 = $searchUseCase->execute('id:' . $id1);
        IndexUseCaseIntegrationTest::assertNotEmpty($results1);
        IndexUseCaseIntegrationTest::assertSame('test-integration.com', $results1[0]->getDomain());

        // Vérification Doc 2 (caractères spéciaux)
        $results2 = $searchUseCase->execute('id:' . $id2);
        IndexUseCaseIntegrationTest::assertNotEmpty($results2);
        IndexUseCaseIntegrationTest::assertSame('Titre avec "guillemets" & spéciaux : éàïô €', $results2[0]->getTitle());
        IndexUseCaseIntegrationTest::assertSame($longContent, $results2[0]->getContent());

        // Vérification Doc 3 (vide)
        $results3 = $searchUseCase->execute('id:' . $id3);
        IndexUseCaseIntegrationTest::assertNotEmpty($results3);
        IndexUseCaseIntegrationTest::assertSame('', $results3[0]->getContent());
    }
}
