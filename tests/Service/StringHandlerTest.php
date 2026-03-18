<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\StringHandler;
use PHPUnit\Framework\TestCase;

class StringHandlerTest extends TestCase
{
    private StringHandler $stringHandler;

    protected function setUp(): void
    {
        $this->stringHandler = new StringHandler();
    }

    public function testExtractStringValue(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => ['value2', 'other'],
            'key3' => 123,
        ];

        self::assertEquals('value1', $this->stringHandler->extractStringValue($data, 'key1'));
        self::assertEquals('value2', $this->stringHandler->extractStringValue($data, 'key2'));
        self::assertEquals('123', $this->stringHandler->extractStringValue($data, 'key3'));
        self::assertEquals('', $this->stringHandler->extractStringValue($data, 'non_existent'));
    }

    public function testSanitizeHighlight(): void
    {
        $prefix = '<em class="hl">';
        $postfix = '</em>';
        $highlight = 'Texte avec <em class="hl">mot</em> et <script>alert("xss")</script>';

        $expected = 'Texte avec <em class="hl">mot</em> et &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';

        $result = $this->stringHandler->sanitizeHighlight($highlight, $prefix, $postfix);

        self::assertEquals($expected, $result);
    }
}
