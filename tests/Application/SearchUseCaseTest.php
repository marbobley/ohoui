<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\TestCase;

class SearchUseCaseTest extends TestCase
{
    public function testExecuteDelegatesToSearchEngine(): void
    {
        $query = 'test';
        $documents = [
            DocumentFactory::create(),
        ];
        $expectedResults = new SearchResult(
            documents: $documents,
            totalCount: 1,
            limit: 10,
            offset: 0
        );

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects($this->once())
            ->method('search')
            ->with($query, 0, 10)
            ->willReturn($expectedResults);

        $useCase = new SearchUseCase($searchEngineMock);
        $results = $useCase->execute($query);

        static::assertSame($expectedResults, $results);
    }
}
