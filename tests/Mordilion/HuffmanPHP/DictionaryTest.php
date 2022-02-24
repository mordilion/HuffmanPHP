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

        self::assertEquals('1', $dictionaryValues['a']);
        self::assertEquals('000', $dictionaryValues['b']);
        self::assertEquals('010', $dictionaryValues['c']);
        self::assertEquals('001', $dictionaryValues['d']);
        self::assertEquals('011', $dictionaryValues['e']);

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

        self::assertEquals('1', $dictionaryValues['a']);
        self::assertEquals('01', $dictionaryValues['b']);
        self::assertEquals('00', $dictionaryValues['c']);
    }

    public function testDictionaryHasTheCorrectStableSortedValues()
    {
        $dictionary = new Dictionary(['aabc'], 1);
        $dictionaryValues = $dictionary->getValues();
        var_dump($dictionaryValues);

        self::assertEquals('1', $dictionaryValues['a']);
        self::assertEquals('01', $dictionaryValues['b']);
        self::assertEquals('00', $dictionaryValues['c']);
    }

    public function testDictionaryHasTheCorrectSortedValuesWithIntegerValues()
    {
        $dictionary = new Dictionary(['123412341567156718961896147258369877655554444433333322222221111'], 1);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals('000', $dictionaryValues['1']);
        self::assertEquals('001', $dictionaryValues['2']);
        self::assertEquals('010', $dictionaryValues['3']);
        self::assertEquals('011', $dictionaryValues['4']);
        self::assertEquals('101', $dictionaryValues['5']);
        self::assertEquals('110', $dictionaryValues['6']);
        self::assertEquals('111', $dictionaryValues['7']);
        self::assertEquals('1000', $dictionaryValues['8']);
        self::assertEquals('1001', $dictionaryValues['9']);

        $dictionary = new Dictionary(['1234123415671567189618961472583698776555544444333333222222211111111111111111111111111111111111111111111111111111111111111'], 1);
        $dictionaryValues = $dictionary->getValues();

        self::assertEquals('0', $dictionaryValues['1']);
        self::assertEquals('111', $dictionaryValues['2']);
        self::assertEquals('1000', $dictionaryValues['3']);
        self::assertEquals('1001', $dictionaryValues['4']);
        self::assertEquals('1011', $dictionaryValues['5']);
        self::assertEquals('1100', $dictionaryValues['6']);
        self::assertEquals('1101', $dictionaryValues['7']);
        self::assertEquals('10100', $dictionaryValues['8']);
        self::assertEquals('10101', $dictionaryValues['9']);
    }
}
