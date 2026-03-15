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

    public function testSearchWithHighlightingDisplaysEmTag(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $indexUseCase = $container->get(\App\Application\Service\IndexUseCase::class);

        $term = 'specifique' . \uniqid();
        $indexUseCase->execute(new \App\Domain\Model\Document(
            'hl-test',
            'Titre avec ' . $term,
            'https://test.com/hl',
            'Le contenu contient le mot ' . $term . ' pour le test.',
            'fr',
            'test.com'
        ));

        $client->request('GET', '/?q=' . $term);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('em.hl');
        $this->assertSelectorTextContains('em.hl', $term);
    }
}
