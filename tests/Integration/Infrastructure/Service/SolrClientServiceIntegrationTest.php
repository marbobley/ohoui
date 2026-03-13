<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service;

use App\Service\SolrClientServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SolrClientServiceIntegrationTest extends KernelTestCase
{
    private SolrClientServiceInterface $solrClientService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->solrClientService = self::getContainer()->get(SolrClientServiceInterface::class);
    }

    public function testGetStatusReturnsRealData(): void
    {
        $status = $this->solrClientService->getStatus();

        $this->assertArrayHasKey('core_name', $status);
        $this->assertArrayHasKey('num_docs', $status);
        $this->assertArrayHasKey('index_size', $status);
        $this->assertArrayHasKey('last_modified', $status);
        $this->assertArrayHasKey('uptime', $status);

        $this->assertIsInt($status['num_docs']);
        // On s'attend à ce que le core soit configuré
        $this->assertNotEmpty($status['core_name']);
        $this->assertNotSame('unknown', $status['core_name']);
    }

    public function testIndexAndPurge(): void
    {
        // On purge d'abord pour être sûr de l'état
        $this->solrClientService->purge();

        $initialStatus = $this->solrClientService->getStatus();
        $this->assertSame(0, $initialStatus['num_docs']);

        // Indexation d'un document de test
        $this->solrClientService->indexDocument([
            'id' => 'test-integration-1',
            'title' => 'Test Integration',
            'url' => 'https://example.com/test',
            'content' => 'Content for integration test',
            'language' => 'fr',
            'domain' => 'example.com',
        ]);

        $afterIndexStatus = $this->solrClientService->getStatus();
        $this->assertSame(1, $afterIndexStatus['num_docs']);

        // Purge finale
        $this->solrClientService->purge();
        $afterPurgeStatus = $this->solrClientService->getStatus();
        $this->assertSame(0, $afterPurgeStatus['num_docs']);
    }
}
