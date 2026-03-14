<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command;

use App\Service\SolrClientServiceInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class IndexSampleCommandIntegrationTest extends KernelTestCase
{
    private SolrClientServiceInterface $solrClientService;

    #[Override]
    protected function setUp(): void
    {
        self::bootKernel();
        $this->solrClientService = self::getContainer()->get(SolrClientServiceInterface::class);
        $this->solrClientService->purge();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->solrClientService->purge();
        parent::tearDown();
    }

    public function testExecuteDefault(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:index-sample');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        // Par défaut il y a 8 échantillons dans le code
        $this->assertStringContainsString('8 documents indexés avec succès.', $output);

        $status = $this->solrClientService->getStatus();
        $this->assertSame(8, $status['num_docs']);
    }

    public function testExecuteWithAdditionalCount(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:index-sample');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--count' => 5,
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        // 8 échantillons + 5 aléatoires = 13
        $this->assertStringContainsString('13 documents indexés avec succès.', $output);

        $status = $this->solrClientService->getStatus();
        $this->assertSame(13, $status['num_docs']);
    }
}
