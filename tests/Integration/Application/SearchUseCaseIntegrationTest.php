<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use App\Service\SolrClientServiceInterface;
use Solarium\QueryType\Select\Result\Result;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SearchUseCaseIntegrationTest extends KernelTestCase
{
    public function testSearchUseCaseIntegration(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $indexUseCase = $container->get(\App\Application\Service\IndexUseCase::class);
        $searchUseCase = $container->get(SearchUseCase::class);
        $this->assertInstanceOf(SearchUseCase::class, $searchUseCase);

        $id = 'test-search-' . uniqid();
        $document = new Document(
            $id,
            'Titre Recherche Intégration',
            'https://search-integration.test',
            'Contenu de test pour la recherche intégrée',
            'fr',
            'search-integration.test'
        );

        // On indexe un document pour être sûr de le trouver
        $indexUseCase->execute($document);

        // Exécution du use case de recherche
        $results = $searchUseCase->execute('id:' . $id);

        // Vérifications
        $this->assertIsArray($results);
        $this->assertNotEmpty($results, 'La recherche devrait retourner au moins un résultat.');

        $foundDoc = null;
        foreach ($results as $doc) {
            if ($doc->getId() === $id) {
                $foundDoc = $doc;
                break;
            }
        }

        $this->assertNotNull($foundDoc, 'Le document indexé devrait être trouvé.');
        $this->assertSame('Titre Recherche Intégration', $foundDoc->getTitle());
    }
}
