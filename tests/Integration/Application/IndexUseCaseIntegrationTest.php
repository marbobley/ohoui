<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\IndexUseCase;
use App\Domain\Model\Document;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IndexUseCaseIntegrationTest extends KernelTestCase
{
    public function testIndexUseCaseIntegration(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var IndexUseCase $indexUseCase */
        $indexUseCase = $container->get(IndexUseCase::class);
        static::assertInstanceOf(IndexUseCase::class, $indexUseCase);

        $id = 'test-id-' . \uniqid();
        $document = new Document(
            $id,
            'Test Indexation Réelle',
            'https://test-integration.com',
            'Contenu de test pour intégration réelle',
            'fr',
            'test-integration.com',
        );

        // Exécution du use case d'indexation (appel réel à Solr)
        $indexUseCase->execute($document);

        // On peut vérifier que le document est bien indexé en le cherchant via le SearchUseCase
        $searchUseCase = $container->get(\App\Application\Service\SearchUseCase::class);
        $results = $searchUseCase->execute('id:' . $id);

        static::assertNotEmpty($results, 'Le document devrait être trouvé dans Solr après indexation.');
        static::assertSame('Test Indexation Réelle', $results[0]->getTitle());
    }
}
