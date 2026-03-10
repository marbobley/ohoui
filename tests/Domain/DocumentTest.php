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

        static::assertSame($id, $document->getId());
        static::assertSame($title, $document->getTitle());
        static::assertSame($url, $document->getUrl());
        static::assertSame($content, $document->getContent());
        static::assertSame($language, $document->getLanguage());
        static::assertSame($domain, $document->getDomain());
    }
}
