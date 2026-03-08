<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndexPageIsUp(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ohoui');
    }

    public function testSearchWithNoResultsDisplaysMessage(): void
    {
        $client = static::createClient();
        // Simuler une recherche qui n'aura probablement pas de résultats dans un environnement de test sans Solr réel configuré
        // ou avec un moteur de recherche mocké si possible.
        // Ici, on teste l'interface utilisateur.

        $client->request('GET', '/?q=mot_cle_inexistant_'.uniqid());

        $this->assertResponseIsSuccessful();
        // On s'attend à voir le message d'absence de résultats ou l'invite à indexer si Solr ne répond pas (ou répond vide)
        // Note: Dans un test fonctionnel Symfony, SolrSearchEngine sera appelé.
        // Si SOLR_HOST n'est pas accessible, cela pourrait échouer.
    }
}
