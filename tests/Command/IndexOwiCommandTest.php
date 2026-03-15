<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Service\ImportOwiUseCase;
use App\Command\IndexOwiCommand;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class IndexOwiCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $dataset = 'test-dataset';
        $limit = 5;

        $importOwiUseCaseMock = $this->createMock(ImportOwiUseCase::class);
        $importOwiUseCaseMock->expects(self::once())
            ->method('execute')
            ->with($dataset, $limit)
            ->willReturn(5);

        $command = new IndexOwiCommand($importOwiUseCaseMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dataset' => $dataset,
            '--limit' => $limit,
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Indexation des données OWI', $output);
        self::assertStringContainsString('5 documents ont été indexés avec succès.', $output);
    }

    public function testExecuteFailure(): void
    {
        $importOwiUseCaseMock = $this->createMock(ImportOwiUseCase::class);
        $importOwiUseCaseMock->expects(self::once())
            ->method('execute')
            ->willThrowException(new Exception('Docker error'));

        $command = new IndexOwiCommand($importOwiUseCaseMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Une erreur est survenue lors de l\'importation : Docker error', $output);
    }
}
