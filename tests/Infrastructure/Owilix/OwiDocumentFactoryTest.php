<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Owilix;

use App\Domain\Model\Document;
use App\Infrastructure\Owilix\Exception\OwiInvalidJsonException;
use App\Infrastructure\Owilix\Exception\OwiLineSkippedException;
use App\Infrastructure\Owilix\Exception\OwiMissingUrlException;
use App\Infrastructure\Owilix\OwiDocumentFactory;
use PHPUnit\Framework\TestCase;

class OwiDocumentFactoryTest extends TestCase
{
    private OwiDocumentFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new OwiDocumentFactory();
    }

    public function testFromLineWithEmptyLineThrowsException(): void
    {
        $this->expectException(OwiLineSkippedException::class);
        $this->factory->fromLine('  ');
    }

    public function testFromLineWithSuccessMessageThrowsException(): void
    {
        $this->expectException(OwiLineSkippedException::class);
        $this->factory->fromLine('✅ Processing completed successfully with no errors!');
    }

    public function testFromLineWithInvalidJsonThrowsException(): void
    {
        $this->expectException(OwiInvalidJsonException::class);
        $this->factory->fromLine('invalid json');
    }

    public function testFromLineWithMissingUrlThrowsException(): void
    {
        $this->expectException(OwiMissingUrlException::class);
        $this->factory->fromLine('{"id": "123", "title": "Test"}');
    }

    public function testFromLineWithEmptyUrlThrowsException(): void
    {
        $this->expectException(OwiMissingUrlException::class);
        $this->factory->fromLine('{"url": "", "id": "123"}');
    }

    public function testFromLineWithMinimalValidDataReturnsDocumentWithDefaults(): void
    {
        $url = 'https://example.com/page';
        $line = json_encode(['url' => $url]);

        $document = $this->factory->fromLine($line);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($url, $document->getUrl());
        $this->assertEquals(md5($url), $document->getId());
        $this->assertEquals('Sans titre', $document->getTitle());
        $this->assertEquals('', $document->getContent());
        $this->assertEquals('unknown', $document->getLanguage());
        $this->assertEquals('example.com', $document->getDomain());
    }

    public function testFromLineWithFullValidDataReturnsDocumentWithValues(): void
    {
        $data = [
            'id' => 'custom-id',
            'title' => 'Custom Title',
            'url' => 'https://example.com/page',
            'main_content' => 'Hello world',
            'language' => 'fr',
            'url_domain' => 'my-domain.com'
        ];
        $line = json_encode($data);

        $document = $this->factory->fromLine($line);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($data['id'], $document->getId());
        $this->assertEquals($data['title'], $document->getTitle());
        $this->assertEquals($data['url'], $document->getUrl());
        $this->assertEquals($data['main_content'], $document->getContent());
        $this->assertEquals($data['language'], $document->getLanguage());
        $this->assertEquals($data['url_domain'], $document->getDomain());
    }

    public function testFromLineWithDomainFromUrlIfMissingInJson(): void
    {
        $url = 'https://custom-domain.org/foo';
        $line = json_encode(['url' => $url]);

        $document = $this->factory->fromLine($line);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('custom-domain.org', $document->getDomain());
    }

    public function testFromLineWithUnknownDomainIfParseUrlFails(): void
    {
        // Une URL qui ne contient pas d'hôte
        $url = 'not-a-valid-url';
        $line = json_encode(['url' => $url]);

        $document = $this->factory->fromLine($line);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('unknown', $document->getDomain());
    }
}
