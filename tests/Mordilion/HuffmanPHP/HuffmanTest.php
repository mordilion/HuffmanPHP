<?php

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

use PHPUnit\Framework\TestCase;

/**
 * @author Henning Huncke <henning.huncke@check24.de>
 */
class HuffmanTest extends TestCase
{
    public function testSuccessSimpleDecodingWithMaxLengthOfOne()
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $huffman = new Huffman($dictionary);

        $original = 'aaaaaaaaaabbcc';
        $encoded = $huffman->encode($original);

        self::assertEquals($original, $huffman->decode($encoded));
    }

    public function testSuccessDecodingWithMaxLengthOfSixteenWithMultipleValues()
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
}
