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

        SearchUseCaseIntegrationTest::assertNotEmpty($results->getDocuments());
        SearchUseCaseIntegrationTest::assertTrue(
            array_any($results->getDocuments(), fn($d) => $d->getTitle() === 'Le Web Décentralisé en 2026')
        );
        SearchUseCaseIntegrationTest::assertGreaterThanOrEqual(1, $results->getTotalCount());
    }

    public function testSearchByLanguage(): void
    {
        $doc = DocumentFactory::create(language: 'en');
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('language:en');

        SearchUseCaseIntegrationTest::assertNotEmpty($results->getDocuments());
        SearchUseCaseIntegrationTest::assertContainsOnlyInstancesOf(Document::class, $results->getDocuments());
    }

    public function testSearchById(): void
    {
        $doc = DocumentFactory::create();
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('id:' . $doc->getId());

        SearchUseCaseIntegrationTest::assertCount(1, $results->getDocuments());
        SearchUseCaseIntegrationTest::assertSame($doc->getId(), $results->getDocuments()[0]->getId());
    }

    public function testSearchWithSpecialCharactersInField(): void
    {
        $doc = DocumentFactory::create(title: 'Décentralisé');
        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('title:Décentralisé');

        SearchUseCaseIntegrationTest::assertNotEmpty($results->getDocuments());
        SearchUseCaseIntegrationTest::assertTrue(array_any($results->getDocuments(), fn($d) => $d->getId() === $doc->getId()));
    }

    public function testSearchRankingBoostsTitle(): void
    {
        // Document avec le mot clé dans le contenu
        $docContent = DocumentFactory::create(
            id: 'rank-1',
            title: 'Un article quelconque',
            content: 'Ici on parle de BoostKeyword et de ses nouveautés.'
        );
        // Document avec le mot clé dans le titre
        $docTitle = DocumentFactory::create(
            id: 'rank-2',
            title: 'Le guide complet de BoostKeyword',
            content: 'Un guide sur la programmation.'
        );

        $this->indexUseCase->execute($docContent);
        $this->indexUseCase->execute($docTitle);

        $results = $this->searchUseCase->execute('BoostKeyword');

        SearchUseCaseIntegrationTest::assertGreaterThanOrEqual(2, count($results->getDocuments()));
        // Le document avec le mot-clé dans le titre doit être en première position grâce au boost
        SearchUseCaseIntegrationTest::assertSame($docTitle->getId(), $results->getDocuments()[0]->getId());
    }

    public function testPagination(): void
    {
        // Indexer plusieurs documents
        for ($i = 0; $i < 15; ++$i) {
            $doc = DocumentFactory::create(id: 'pag-' . $i, title: 'Pagination test doc');
            $this->indexUseCase->execute($doc);
        }

        // Page 1
        $resultsPage1 = $this->searchUseCase->execute('title:"Pagination test doc"', 0, 10);
        SearchUseCaseIntegrationTest::assertCount(10, $resultsPage1->getDocuments());
        SearchUseCaseIntegrationTest::assertGreaterThanOrEqual(15, $resultsPage1->getTotalCount());

        // Page 2
        $resultsPage2 = $this->searchUseCase->execute('title:"Pagination test doc"', 10, 10);
        SearchUseCaseIntegrationTest::assertCount(5, $resultsPage2->getDocuments());
        SearchUseCaseIntegrationTest::assertGreaterThanOrEqual(15, $resultsPage2->getTotalCount());

        // Vérifier que les documents sont différents
        $idsPage1 = array_map(fn($d) => $d->getId(), $resultsPage1->getDocuments());
        $idsPage2 = array_map(fn($d) => $d->getId(), $resultsPage2->getDocuments());

        foreach ($idsPage2 as $id) {
            SearchUseCaseIntegrationTest::assertNotContains($id, $idsPage1);
        }
    }
}
