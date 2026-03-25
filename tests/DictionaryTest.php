<?php

declare(strict_types=1);

use InvalidArgumentException;
use Mordilion\HuffmanPHP\Dictionary;
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

    public function testSingleCharacterDictionaryAssignsBinaryCode(): void
    {
        $dictionary = new Dictionary(['aaa'], 1);
        $dictionaryValues = $dictionary->getValues();

        self::assertCount(1, $dictionaryValues);
        self::assertEquals('0', $dictionaryValues['a']);
    }

    public function testEmptyArrayProducesEmptyDictionary(): void
    {
        $dictionary = new Dictionary([]);
        $dictionaryValues = $dictionary->getValues();

        self::assertEmpty($dictionaryValues);
    }

    public function testGetBinaryReturnsCorrectValueForKnownKey(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        self::assertNotNull($dictionary->getBinary('a'));
        self::assertNotNull($dictionary->getBinary('b'));
        self::assertNotNull($dictionary->getBinary('c'));
    }

    public function testGetBinaryReturnsNullForUnknownKey(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        self::assertNull($dictionary->getBinary('z'));
    }

    public function testGetValueReturnsCorrectKeyForKnownBinary(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        $binaryA = $dictionary->getBinary('a');
        self::assertNotNull($binaryA);
        self::assertEquals('a', $dictionary->getValue($binaryA));
    }

    public function testGetValueReturnsFalseForUnknownBinary(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        self::assertFalse($dictionary->getValue('11111111'));
    }

    public function testGetMinBinaryLengthReturnsCorrectValue(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        // 'a' has the shortest binary code (most frequent)
        $minLength = $dictionary->getMinBinaryLength();
        self::assertIsInt($minLength);
        self::assertGreaterThan(0, $minLength);

        // minBinaryLength should match the shortest binary code
        $values = $dictionary->getValues();
        $shortestLength = PHP_INT_MAX;
        foreach ($values as $binary) {
            $shortestLength = min($shortestLength, strlen($binary));
        }
        self::assertEquals($shortestLength, $minLength);
    }

    public function testSetValuesReplacesAndPreparesValues(): void
    {
        $dictionary = new Dictionary(['aaabbcc'], 1);

        $customValues = ['x' => '0', 'y' => '10', 'z' => '11'];
        $dictionary->setValues($customValues);

        self::assertEquals('0', $dictionary->getBinary('x'));
        self::assertEquals('10', $dictionary->getBinary('y'));
        self::assertEquals('11', $dictionary->getBinary('z'));
        self::assertNull($dictionary->getBinary('a'));
    }

    public function testConstructorThrowsExceptionForNegativeMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Dictionary(['test'], -1);
    }

    public function testMaxLengthWholeWordsIsAccepted(): void
    {
        $dictionary = new Dictionary(['hello', 'world'], Dictionary::MAX_LENGTH_WHOLE_WORDS);
        $values = $dictionary->getValues();

        self::assertArrayHasKey('hello', $values);
        self::assertArrayHasKey('world', $values);
    }

    public function testGetMaxLengthReturnsConstructorValue(): void
    {
        $dictionary = new Dictionary(['test'], 5);
        self::assertEquals(5, $dictionary->getMaxLength());

        $dictionary = new Dictionary(['test'], Dictionary::MAX_LENGTH_WHOLE_WORDS);
        self::assertEquals(Dictionary::MAX_LENGTH_WHOLE_WORDS, $dictionary->getMaxLength());
    }
}
