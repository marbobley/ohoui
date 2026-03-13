<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Service\SolrHealthUseCase;
use App\Command\SolrHealthCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class SolrHealthCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $status = [
            'core_name' => 'test_core',
            'num_docs' => 128,
            'index_size' => '10MB',
            'last_modified' => '2023-10-27 10:00:00',
            'uptime' => 12345,
        ];

        $healthUseCaseMock = $this->createMock(SolrHealthUseCase::class);
        $healthUseCaseMock->expects(self::once())
            ->method('execute')
            ->willReturn($status);

        $command = new SolrHealthCommand($healthUseCaseMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Diagnostic de santé Solr', $output);
        self::assertStringContainsString('Connexion à Solr établie.', $output);
        self::assertStringContainsString('test_core', $output);
        self::assertStringContainsString('128', $output);
    }

    public function testExecuteFailure(): void
    {
        $healthUseCaseMock = $this->createMock(SolrHealthUseCase::class);
        $healthUseCaseMock->expects(self::once())
            ->method('execute')
            ->willThrowException(new \Exception('Connection refused'));

        $command = new SolrHealthCommand($healthUseCaseMock);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Impossible de contacter Solr : Connection refused', $output);
    }
}
