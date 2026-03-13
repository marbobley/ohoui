<?php

declare(strict_types=1);

namespace App\Tests\Application\Service;

use App\Application\Service\SolrHealthUseCase;
use App\Domain\Repository\SearchEngineInterface;
use PHPUnit\Framework\TestCase;

final class SolrHealthUseCaseTest extends TestCase
{
    public function testExecuteReturnsStatusFromSearchEngine(): void
    {
        $expectedStatus = [
            'core_name' => 'test_core',
            'num_docs' => 128,
            'index_size' => '10MB',
            'last_modified' => '2023-10-27 10:00:00',
            'uptime' => 12345,
        ];

        $searchEngineMock = $this->createMock(SearchEngineInterface::class);
        $searchEngineMock->expects(self::once())
            ->method('getStatus')
            ->willReturn($expectedStatus);

        $useCase = new SolrHealthUseCase($searchEngineMock);
        $status = $useCase->execute();

        self::assertSame($expectedStatus, $status);
    }
}
