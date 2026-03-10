<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\IndexUseCase;
use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use App\Tests\Util\DocumentFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function array_any;

final class SearchUseCaseIntegrationTest extends KernelTestCase
{
    private IndexUseCase $indexUseCase;
    private SearchUseCase $searchUseCase;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->indexUseCase = $container->get(IndexUseCase::class);
        $this->searchUseCase = $container->get(SearchUseCase::class);
    }

    public function testSearchByKeywordInTitle(): void
    {
        $doc = DocumentFactory::create(title:'Le Web Décentralisé en 2026');
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('Décentralisé');

        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertSame('Le Web Décentralisé en 2026', $results[0]->getTitle());
    }

    public function testSearchByLanguage(): void
    {
        $doc = DocumentFactory::create(language: 'en');
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('language:en');

        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertContainsOnlyInstancesOf(Document::class, $results);
    }

    public function testSearchById(): void
    {
        $doc = DocumentFactory::create();
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('id:' . $doc->getId());

        SearchUseCaseIntegrationTest::assertCount(1, $results);
        SearchUseCaseIntegrationTest::assertSame($doc->getId(), $results[0]->getId());
    }

    public function testSearchWithSpecialCharactersInField(): void
    {
        $doc = DocumentFactory::create(title: 'Décentralisé');
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('title:Décentralisé');

        SearchUseCaseIntegrationTest::assertNotEmpty($results);
        SearchUseCaseIntegrationTest::assertTrue(array_any($results, fn($d) => $d->getId() === $doc->getId()));
    }
}
