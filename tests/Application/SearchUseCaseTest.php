<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use App\Domain\Repository\SearchEngineInterface;
use PHPUnit\Framework\TestCase;

class SearchUseCaseTest extends TestCase
{
    public function testExecute(): void
    {
        $query = 'test';
        $expectedResults = [
            new Document('1', 'Title', 'Url', 'Content', 'fr', 'domain.com'),
        ];

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects($this->once())->method('search')->with($query)->willReturn($expectedResults);

        $useCase = new SearchUseCase($searchEngineMock);
        $results = $useCase->execute($query);

        static::assertSame($expectedResults, $results);
    }
}
