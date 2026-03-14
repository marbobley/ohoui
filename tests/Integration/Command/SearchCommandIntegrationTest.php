<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Service\SolrClientServiceInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SearchCommandIntegrationTest extends KernelTestCase
{
    private SolrClientServiceInterface $solrClientService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->solrClientService = self::getContainer()->get(SolrClientServiceInterface::class);

        // Nettoyage avant chaque test
        $this->solrClientService->purge();
    }

    protected function tearDown(): void
    {
        // Nettoyage après chaque test
        $this->solrClientService->purge();
        parent::tearDown();
    }

    public function testExecuteSuccess(): void
    {
        // Indexation d'un document de test
        $this->solrClientService->indexDocument([
            'id' => 'test-search-1',
            'title' => 'Ceci est un test de recherche',
            'url' => 'https://example.com/test-search',
            'content' => 'Le contenu du test contient le mot clé PHP et Symfony.',
            'language' => 'fr',
            'domain' => 'example.com',
        ]);

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:search');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'query' => 'PHP',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Recherche pour : "PHP"', $output);
        $this->assertStringContainsString('Trouvé 1 document(s)', $output);
        $this->assertStringContainsString('Ceci est un test de recherche', $output);
        $this->assertStringContainsString('https://example.com/test-search', $output);
        $this->assertStringContainsString('example.com', $output);
    }

    public function testExecuteNoResults(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:search');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'query' => 'MotCleInexistantDansSolr',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Recherche pour : "MotCleInexistantDansSolr"', $output);
        $this->assertStringContainsString('Aucun résultat trouvé.', $output);
    }
}
