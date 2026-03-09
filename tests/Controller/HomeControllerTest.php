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

        // On indexe au moins un document pour initialiser les champs dans Solr (dynamic fields)
        // et éviter l'erreur "undefined field title" si le core est vide.
        $container = static::getContainer();
        $indexUseCase = $container->get(\App\Application\Service\IndexUseCase::class);
        $indexUseCase->execute(new \App\Domain\Model\Document(
            'init-test',
            'Initialisation Solr',
            'https://test.com',
            'Contenu initial pour créer les champs',
            'fr',
            'test.com'
        ));

        $client->request('GET', '/?q=mot_cle_inexistant_' . \uniqid());

        $this->assertResponseIsSuccessful();
    }
}
