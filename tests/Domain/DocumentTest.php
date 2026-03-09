<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\Model\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testItCanBeCreated(): void
    {
        $id = '123';
        $title = 'Titre de test';
        $url = 'https://example.com';
        $content = 'Contenu de test';
        $language = 'fr';
        $domain = 'example.com';

        $document = new Document($id, $title, $url, $content, $language, $domain);

        $this->assertSame($id, $document->getId());
        $this->assertSame($title, $document->getTitle());
        $this->assertSame($url, $document->getUrl());
        $this->assertSame($content, $document->getContent());
        $this->assertSame($language, $document->getLanguage());
        $this->assertSame($domain, $document->getDomain());
    }
}
