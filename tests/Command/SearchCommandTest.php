<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Dto\CategorizedSearchResults;
use App\Application\Service\SearchUseCase;
use App\Command\SearchCommand;
use App\Domain\Model\Document;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class SearchCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $query = 'php';
        $documents = [
            new Document('1', 'PHP is great', 'https://php.net', 'PHP content', 'fr', 'php.net'),
            new Document('2', 'Symfony framework', 'https://symfony.com', 'Symfony content', 'fr', 'symfony.com'),
        ];
        $searchResultRaw = new SearchResult($documents, 2, 10, 0);

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('search')
            ->willReturn($searchResultRaw);

        $searchUseCase = new SearchUseCase($searchEngineMock);

        $command = new SearchCommand($searchUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['query' => $query]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Recherche pour : "php"', $output);
        self::assertStringContainsString('Trouvé 2 document(s) (total: 2)', $output);
        self::assertStringContainsString('PHP is great', $output);
        self::assertStringContainsString('https://symfony.com', $output);
    }

    public function testExecuteNoResults(): void
    {
        $query = 'unknown';
        $searchResultRaw = new SearchResult([], 0, 10, 0);

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('search')
            ->willReturn($searchResultRaw);

        $searchUseCase = new SearchUseCase($searchEngineMock);

        $command = new SearchCommand($searchUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['query' => $query]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Aucun résultat trouvé.', $output);
    }

    public function testExecuteFailure(): void
    {
        $query = 'error';
        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('search')
            ->willThrowException(new \Exception('Solr error'));

        $searchUseCase = new SearchUseCase($searchEngineMock);

        $command = new SearchCommand($searchUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['query' => $query]);

        self::assertEquals(1, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Une erreur est survenue lors de la recherche : Solr error', $output);
    }

    public function testExecuteWithFilters(): void
    {
        $query = 'php';
        $documents = [
            new Document('1', 'PHP is great', 'https://php.net', 'PHP content', 'fr', 'php.net'),
        ];
        $searchResultRaw = new SearchResult($documents, 1, 10, 0);

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('search')
            ->willReturn($searchResultRaw);

        $searchUseCase = new SearchUseCase($searchEngineMock);

        $command = new SearchCommand($searchUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'query' => $query,
            '--filter' => ['domain:php.net', 'invalid_filter'],
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Recherche pour : "php"', $output);
        self::assertStringContainsString('Filtres actifs :', $output);
        self::assertStringContainsString('- domain: php.net', $output);
        self::assertStringNotContainsString('invalid_filter', $output);
        self::assertStringContainsString('Trouvé 1 document(s) (total: 1)', $output);
    }

    public function testExecuteWithPagination(): void
    {
        $query = 'php';
        $limit = 5;
        $offset = 10;
        $searchResultRaw = new SearchResult([], 0, $limit, $offset);

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('search')
            ->willReturn($searchResultRaw);

        $searchUseCase = new SearchUseCase($searchEngineMock);

        $command = new SearchCommand($searchUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'query' => $query,
            '--limit' => $limit,
            '--offset' => $offset,
        ]);

        $commandTester->assertCommandIsSuccessful();
    }
}
