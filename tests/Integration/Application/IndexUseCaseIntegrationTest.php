<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\Service\IndexUseCase;
use App\Application\Service\SearchUseCase;
use App\Domain\Model\Document;
use App\Tests\Util\DocumentFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function str_repeat;

final class IndexUseCaseIntegrationTest extends KernelTestCase
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

    public function testIndexStandardDocument(): void
    {
        $doc = DocumentFactory::create(
            title: 'Test Indexation Réelle',
            url: 'https://test-integration.com',
            content: 'Contenu de test pour intégration réelle',
            domain: 'test-integration.com'
        );

        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('id:' . $doc->getId());
        static::assertNotEmpty($results);
        static::assertSame('test-integration.com', $results[0]->getDomain());
    }

    public function testIndexDocumentWithSpecialCharactersAndLongContent(): void
    {
        $longContent = str_repeat('Ceci est un contenu très long pour tester la capacité de stockage de Solr. ', 100);
        $doc = DocumentFactory::create(
            title: 'Titre avec "guillemets" & spéciaux : éàïô €',
            url: 'https://test-special.com/path?query=1',
            content: $longContent,
            language: 'en',
            domain: 'test-special.com'
        );

        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('id:' . $doc->getId());
        static::assertNotEmpty($results);
        static::assertSame('Titre avec "guillemets" & spéciaux : éàïô €', $results[0]->getTitle());
        static::assertSame($longContent, $results[0]->getContent());
    }

    public function testIndexDocumentWithMinimalContent(): void
    {
        $doc = DocumentFactory::createWithEmptyContent();

        $this->indexUseCase->execute($doc);

        $results = $this->searchUseCase->execute('id:' . $doc->getId());
        static::assertNotEmpty($results);
        static::assertSame('', $results[0]->getContent());
    }
}
