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
            'aaaaaaaaaaaaaaa',
            'bbbbbbb',
            'cccccc',
            'dddddd',
            'eeeee'
        ];

        $dictionary = new Dictionary($values);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals('0', $dictionaryValues['a']);
        self::assertEquals('100', $dictionaryValues['b']);
        self::assertEquals('110', $dictionaryValues['c']);
        self::assertEquals('101', $dictionaryValues['d']);
        self::assertEquals('111', $dictionaryValues['e']);

        $dictionary = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals('001', $dictionaryValues['aaaaaaaaaaaaaaa']);
        self::assertEquals('000', $dictionaryValues['bbbbbbb']);
        self::assertEquals('11', $dictionaryValues['cccccc']);
        self::assertEquals('10', $dictionaryValues['dddddd']);
        self::assertEquals('01', $dictionaryValues['eeeee']);
    }

    public function testDictionaryHasTheCorrectSortedValuesWithSingleValue()
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals('0', $dictionaryValues['a']);
        self::assertEquals('10', $dictionaryValues['b']);
        self::assertEquals('11', $dictionaryValues['c']);
    }
}
