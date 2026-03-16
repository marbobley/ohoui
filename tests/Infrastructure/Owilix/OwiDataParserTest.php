<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Owilix;

use App\Infrastructure\Owilix\Exception\OwiInvalidJsonException;
use App\Infrastructure\Owilix\Exception\OwiLineSkippedException;
use App\Infrastructure\Owilix\Exception\OwiMissingUrlException;
use App\Infrastructure\Owilix\OwiDocumentFactory;
use PHPUnit\Framework\TestCase;

final class OwiDataParserTest extends TestCase
{
    private OwiDocumentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new OwiDocumentFactory();
    }

    public function testParseLineThrowsExceptionForEmptyLine(): void
    {
        $this->expectException(OwiLineSkippedException::class);
        $this->factory->fromLine('');
    }

    public function testParseLineThrowsExceptionForSuccessMessage(): void
    {
        $this->expectException(OwiLineSkippedException::class);
        $this->factory->fromLine('✅ Processing completed successfully with no errors!');
    }

    public function testParseLineThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(OwiInvalidJsonException::class);
        $this->factory->fromLine('invalid json');
    }

    public function testParseLineThrowsExceptionIfUrlMissing(): void
    {
        $this->expectException(OwiMissingUrlException::class);
        $this->factory->fromLine('{"title": "No URL"}');
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
