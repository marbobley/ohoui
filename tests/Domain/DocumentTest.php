<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Tests\Util\DocumentFactory;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testDocumentState(): void
    {
        $id = '123';
        $title = 'Titre de test';
        $url = 'https://example.com';
        $content = 'Contenu de test';
        $language = 'fr';
        $domain = 'example.com';

        $document = DocumentFactory::create($id, $title, $url, $content, $language, $domain);

        self::assertSame($id, $document->getId());
        self::assertSame($title, $document->getTitle());
        self::assertSame($url, $document->getUrl());
        self::assertSame($content, $document->getContent());
        self::assertSame($language, $document->getLanguage());
        self::assertSame($domain, $document->getDomain());
    }
}
