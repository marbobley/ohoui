<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use App\Domain\Repository\SearchEngineInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\TestCase;

class SearchUseCaseTest extends TestCase
{
    public function testExecuteDelegatesToSearchEngine(): void
    {
        $query = 'test';
        $expectedResults = [
            DocumentFactory::create(id: '1'),
        ];

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects($this->once())
            ->method('search')
            ->with($query)
            ->willReturn($expectedResults);

        $useCase = new SearchUseCase($searchEngineMock);
        $results = $useCase->execute($query);

        static::assertSame($expectedResults, $results);
    }
}
