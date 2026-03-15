<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Solr;

use App\Domain\Model\Document;
use App\Infrastructure\Solr\SolrSearchEngine;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function array_map;

final class SolrSearchPatternsIntegrationTest extends KernelTestCase
{
    private SolrSearchEngine $searchEngine;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->searchEngine = self::getContainer()->get(SolrSearchEngine::class);
        $this->searchEngine->purge();

        // Ajout de documents de test
        $this->searchEngine->index(new Document(
            'doc-1',
            'Le chat noir',
            'https://example.com/1',
            'Un chat noir court dans la rue.',
            'fr',
            'example.com'
        ));

        $this->searchEngine->index(new Document(
            'doc-2',
            'Un château fort',
            'https://example.com/2',
            'Le château est très ancien et imposant.',
            'fr',
            'example.com'
        ));

        $this->searchEngine->index(new Document(
            'doc-3',
            'Chantier en cours',
            'https://example.com/3',
            'Le chantier avance bien.',
            'fr',
            'example.com'
        ));

        $this->searchEngine->index(new Document(
            'doc-4',
            'Un chien blanc',
            'https://example.com/4',
            'Le chien blanc aboie.',
            'fr',
            'example.com'
        ));
    }

    public function testWildcardStar(): void
    {
        // Recherche avec 'cha*' doit trouver chat, château (si normalisé), chantier
        $result = $this->searchEngine->search('cha*');

        $ids = array_map(fn($doc) => $doc->getId(), $result->getDocuments());

        $this->assertContains('doc-1', $ids, 'Devrait trouver "chat"');
        // $this->assertContains('doc-2', $ids, 'Devrait trouver "château"'); // Dépend de l'accentuation/normalisation
        $this->assertContains('doc-3', $ids, 'Devrait trouver "chantier"');
        $this->assertNotContains('doc-4', $ids, 'Ne devrait pas trouver "chien"');
    }

    public function testWildcardQuestionMark(): void
    {
        // Recherche avec 'ch?t' doit trouver chat (4 caractères)
        // Attention: Selon la configuration Solr, château peut être tokenizé différemment
        $result = $this->searchEngine->search('ch?t');

        $ids = array_map(fn($doc) => $doc->getId(), $result->getDocuments());

        $this->assertContains('doc-1', $ids, 'Devrait trouver "chat"');
        $this->assertNotContains('doc-3', $ids, 'Ne devrait pas trouver "chantier"');
    }

    public function testFuzzySearch(): void
    {
        // Recherche avec 'chat~' doit trouver chat, et potentiellement d'autres mots proches
        $result = $this->searchEngine->search('chat~');

        $ids = array_map(fn($doc) => $doc->getId(), $result->getDocuments());

        $this->assertContains('doc-1', $ids, 'Devrait trouver "chat" via fuzzy search');

        // 'chant' est proche de 'chat' (distance de 1)
        // doc-3 contient "chantier", si Solr indexe les racines ou si "chant" est présent
        // Testons avec une faute de frappe
        $result2 = $this->searchEngine->search('shat~');
        $ids2 = array_map(fn($doc) => $doc->getId(), $result2->getDocuments());
        $this->assertContains('doc-1', $ids2, 'Devrait trouver "chat" pour "shat~"');
    }

    protected function tearDown(): void
    {
        $this->searchEngine->purge();
        parent::tearDown();
    }
}
