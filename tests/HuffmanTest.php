<?php

declare(strict_types=1);

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Henning Huncke <henning.huncke@check24.de>
 */
class HuffmanTest extends TestCase
{
    public function testSuccessSimpleDecodingWithMaxLengthOfOne(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaaaaaaaabbcc';
        $encoded = $huffman->encode($original);

        self::assertEquals($original, $huffman->decode($encoded));
    }

    public function testSuccessDecodingWithMaxLengthOfSixteenWithMultipleValues(): void
    {
        $dictionary = new Dictionary([
            'aaabbcc',
            'aaaaaaaaaaaaaaaaaaaaabbbbbbbbcccccccc',
            'ddddddddd',
            'aaaaabc',
        ], 16);
        $huffman = new Huffman($dictionary);

        $original = 'aaaaaaaaaabbcc';
        $encoded = $huffman->encode($original);

        self::assertEquals($original, $huffman->decode($encoded));
    }

    public function testRoundtripWithCompression(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaaaaaaaabbcc';
        $encoded = $huffman->encode($original, true);

        self::assertNotEmpty($encoded);
        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testRoundtripWithCompressionAndCustomAlphabetBase10(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary, Huffman::ALPHABET_BASE10);

        $original = 'aaaabbcc';
        $encoded = $huffman->encode($original, true);

        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testRoundtripWithCompressionAndCustomAlphabetBase36(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary, Huffman::ALPHABET_BASE36);

        $original = 'aaaabbcc';
        $encoded = $huffman->encode($original, true);

        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testRoundtripWithCompressionAndCustomAlphabetBase62(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary, Huffman::ALPHABET_BASE62);

        $original = 'aaabbccaaabbcc';
        $encoded = $huffman->encode($original, true);

        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testEmptyStringEncodeReturnsEmptyString(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        self::assertSame('', $huffman->encode(''));
        self::assertSame('', $huffman->encode('', true));
    }

    public function testEmptyStringDecodeReturnsEmptyString(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        self::assertSame('', $huffman->decode(''));
        self::assertSame('', $huffman->decode('', true));
    }

    public function testSingleCharacterDictionaryRoundtrip(): void
    {
        $dictionary = new Dictionary(['aaaa'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaa';
        $encoded = $huffman->encode($original);

        self::assertNotEmpty($encoded);
        self::assertEquals($original, $huffman->decode($encoded));
    }

    public function testSingleCharacterDictionaryRoundtripWithCompression(): void
    {
        $dictionary = new Dictionary(['aaaa'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaa';
        $encoded = $huffman->encode($original, true);

        self::assertNotEmpty($encoded);
        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testWholeWordsModeRoundtrip(): void
    {
        $text = 'A Text with multiple Text Segments';
        $dictionary = new Dictionary(
            array_merge(array_unique(explode(' ', $text)), [' ']),
            Dictionary::MAX_LENGTH_WHOLE_WORDS
        );
        $huffman = new Huffman($dictionary);

        $encoded = $huffman->encode($text);

        self::assertEquals($text, $huffman->decode($encoded));
    }

    public function testWholeWordsModeRoundtripWithCompression(): void
    {
        $text = 'A Text with multiple Text Segments';
        $dictionary = new Dictionary(
            array_merge(array_unique(explode(' ', $text)), [' ']),
            Dictionary::MAX_LENGTH_WHOLE_WORDS
        );
        $huffman = new Huffman($dictionary);

        $encoded = $huffman->encode($text, true);

        self::assertEquals($text, $huffman->decode($encoded, true));
    }

    public function testCacheReturnsConsistentResults(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aabbcc';

        $encoded1 = $huffman->encode($original);
        $encoded2 = $huffman->encode($original);
        self::assertSame($encoded1, $encoded2);

        $decoded1 = $huffman->decode($encoded1);
        $decoded2 = $huffman->decode($encoded1);
        self::assertSame($decoded1, $decoded2);
    }

    public function testCacheReturnsConsistentResultsWithCompression(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aabbcc';

        $encoded1 = $huffman->encode($original, true);
        $encoded2 = $huffman->encode($original, true);
        self::assertSame($encoded1, $encoded2);

        $decoded1 = $huffman->decode($encoded1, true);
        $decoded2 = $huffman->decode($encoded1, true);
        self::assertSame($decoded1, $decoded2);
    }

    public function testEncodeThrowsExceptionForUnknownCharacter(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $this->expectException(RuntimeException::class);
        $huffman->encode('xyz');
    }

    public function testLongStringRoundtrip(): void
    {
        $original = str_repeat('abcdefg', 100);
        $dictionary = new Dictionary([$original], 1);
        $huffman = new Huffman($dictionary);

        $encoded = $huffman->encode($original);

        self::assertEquals($original, $huffman->decode($encoded));
    }

    public function testLongStringRoundtripWithCompression(): void
    {
        $original = str_repeat('abcdefg', 100);
        $dictionary = new Dictionary([$original], 1);
        $huffman = new Huffman($dictionary);

        $encoded = $huffman->encode($original, true);

        self::assertEquals($original, $huffman->decode($encoded, true));
    }

    public function testCompressionProducesShorterOutput(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaaaaaaaabbcc';
        $binary = $huffman->encode($original, false);
        $compressed = $huffman->encode($original, true);

        self::assertLessThan(strlen($binary), strlen($compressed));
    }
}
