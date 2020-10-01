<?php

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

use PHPUnit\Framework\TestCase;

/**
 * @author Henning Huncke <henning.huncke@check24.de>
 */
class DictionaryTest extends TestCase
{
    public function testDictionaryHasTheCorrectSortedValues(): void
    {
        $values = [
            'a',
            'a',
            'a',
            'a',
            'bcddddd',
            'b',
            'b',
            'b',
            'caaaaaa',
            'c',
            'c',
            'abc',
            'ab',
            'd',
            'baaaaaa',
        ];

        $dictionary = new Dictionary($values);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals(0, bindec($dictionaryValues['a']));
        self::assertEquals(1, bindec($dictionaryValues['d']));
        self::assertEquals(2, bindec($dictionaryValues['c']));
        self::assertEquals(3, bindec($dictionaryValues['b']));

        $dictionary = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS);
        $dictionaryValues = $dictionary->getValues();
        var_dump($dictionaryValues);

        self::assertEquals(0, bindec($dictionaryValues['a']));
        self::assertEquals(1, bindec($dictionaryValues['d']));
        self::assertEquals(2, bindec($dictionaryValues['c']));
        self::assertEquals(3, bindec($dictionaryValues['b']));
    }
}
