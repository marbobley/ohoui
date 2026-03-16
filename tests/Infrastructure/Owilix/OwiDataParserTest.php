<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Owilix;

use App\Infrastructure\Owilix\OwiDocumentFactory;
use PHPUnit\Framework\TestCase;

final class OwiDataParserTest extends TestCase
{
    private OwiDocumentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new OwiDocumentFactory();
    }

    public function testParseLineReturnsNullForEmptyLine(): void
    {
        self::assertNull($this->factory->fromLine(''));
        self::assertNull($this->factory->fromLine('   '));
    }

    public function testParseLineReturnsNullForSuccessMessage(): void
    {
        self::assertNull($this->factory->fromLine('✅ Processing completed successfully with no errors!'));
    }

    public function testParseLineReturnsNullForInvalidJson(): void
    {
        self::assertNull($this->factory->fromLine('invalid json'));
    }

    public function testParseLineReturnsNullIfUrlMissing(): void
    {
        self::assertNull($this->factory->fromLine('{"title": "No URL"}'));
    }

    public function testParseLineReturnsDocument(): void
    {
        $json = '{"url": "https://example.com/page", "title": "Example", "main_content": "Content", "language": "fr", "url_domain": "example.com"}';
        $document = $this->factory->fromLine($json);

        self::assertNotNull($document);
        self::assertEquals('https://example.com/page', $document->getUrl());
        self::assertEquals('Example', $document->getTitle());
        self::assertEquals('Content', $document->getContent());
        self::assertEquals('fr', $document->getLanguage());
        self::assertEquals('example.com', $document->getDomain());
    }

    public function testParseLineResolvesDomainFromUrl(): void
    {
        $json = '{"url": "https://test.com/foo", "title": "Test"}';
        $document = $this->factory->fromLine($json);

        self::assertNotNull($document);
        self::assertEquals('test.com', $document->getDomain());
    }

    public function testParseLineUsesDefaultValues(): void
    {
        $json = '{"url": "https://test.com"}';
        $document = $this->factory->fromLine($json);

        self::assertNotNull($document);
        self::assertEquals('Sans titre', $document->getTitle());
        self::assertEquals('', $document->getContent());
        self::assertEquals('unknown', $document->getLanguage());
    }
}
