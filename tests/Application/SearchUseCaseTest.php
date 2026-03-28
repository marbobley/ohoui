<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Dto\CategorizedSearchResults;
use App\Application\Service\SearchUseCase;
use App\Domain\Model\SearchCriteria;
use App\Domain\Model\SearchResult;
use App\Domain\Repository\SearchEngineInterface;
use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\TestCase;

class SearchUseCaseTest extends TestCase
{
    public function testExecuteDelegatesToSearchEngine(): void
    {
        $query = 'test';
        $page = 1;
        $limit = 10;
        $filters = ['language' => 'fr'];

        $criteria = new SearchCriteria($query, $page, $limit, $filters);

        $documents = [
            DocumentFactory::create(highlight: '<em class="hl">test</em>'),
        ];
        $searchResult = new SearchResult(
            documents: $documents,
            totalCount: 1,
            limit: $limit,
            offset: 0
        );

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects($this->once())
            ->method('search')
            ->willReturn($searchResult);

        $useCase = new SearchUseCase($searchEngineMock);
        $results = $useCase->execute($criteria);

        static::assertInstanceOf(CategorizedSearchResults::class, $results);
        static::assertCount(1, $results->getRelevant());
        static::assertCount(1, $results->getRecent());
        static::assertCount(1, $results->getRandom());
        static::assertSame('<em class="hl">test</em>', $results->getRelevant()[0]->getHighlight());
    }
}
