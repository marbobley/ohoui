<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Domain\Model\Document;
use function uniqid;

final class DocumentFactory
{
    public static function create(
        ?string $id = "1",
        string $title = 'Default Title',
        string $url = 'https://default.test',
        string $content = 'Default Content',
        string $language = 'fr',
        string $domain = 'default.test'
    ): Document {
        return new Document(
            $id ?? 'test-' . uniqid(),
            $title,
            $url,
            $content,
            $language,
            $domain
        );
    }

    public static function createWithSpecialCharacters(): Document
    {
        return self::create(
            title: 'Titre avec "guillemets" & spéciaux : éàïô €',
            url: 'https://test-special.com/path?query=1',
            content: 'Contenu avec caractères spéciaux',
            language: 'en',
            domain: 'test-special.com'
        );
    }

    public static function createWithEmptyContent(): Document
    {
        return self::create(
            title: 'Titre Seul',
            content: '',
            domain: 'empty.test'
        );
    }
}
