<?php

/**
 * This file is part of the Mordilion\HuffmanPHP package.
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 * @copyright (c) Henning Huncke - <mordilion@gmx.de>
 */

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

define('BIG_NUMBERS_LIB_BCMATH', 'bcmath');
define('BIG_NUMBERS_LIB_GMP', 'gmp');
define('BIG_NUMBERS_LIB_NONE', 'none');
define(
    'BIG_NUMBERS_LIB_INSTALLED',
    extension_loaded('gmp')
        ? BIG_NUMBERS_LIB_GMP
        : (
            extension_loaded('bcmath')
                ? BIG_NUMBERS_LIB_BCMATH
                : BIG_NUMBERS_LIB_NONE
        )
);

use RuntimeException;

/**
 * @author Henning Huncke <mordilion@gmx.de>
 */
class Huffman
{
    public const ALPHABET_BASE10 = '0123456789';
    public const ALPHABET_BASE16 = '0123456789ABCDEF';
    public const ALPHABET_BASE16_LOWER = '0123456789abcdef';
    public const ALPHABET_BASE25 = 'ABCDEFGHIJKLMNOPQRSTUVWXY';
    public const ALPHABET_BASE25_LOWER = 'abcdefghijklmnopqrstuvwxyz';
    public const ALPHABET_BASE36 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXY';
    public const ALPHABET_BASE36_LOWER = '0123456789abcdefghijklmnopqrstuvwxyz';
    public const ALPHABET_BASE62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    public const ALPHABET_BASE65 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_~'; // MAX URL-SAFE ALPHABET
    private const ALPHABET_BINARY = '01';

    /**
     * @var array
     */
    private $cache;

    /**
     * @var string
     */
    private $compressAlphabet;

    /**
     * @var Dictionary
     */
    private $dictionary;

    /**
     * Huffman constructor.
     *
     * @param Dictionary $dictionary
     * @param string     $compressAlphabet
     */
    public function __construct(Dictionary $dictionary, string $compressAlphabet = self::ALPHABET_BASE65)
    {
        $this->dictionary = $dictionary;
        $this->compressAlphabet = $compressAlphabet;
    }

    /**
     * @param string $encoded
     * @param bool   $compressed
     *
     * @return string
     */
    public function decode(string $encoded, bool $compressed = false): string
    {
        if ($encoded === '') {
            return '';
        }

        if (isset($this->cache['decode'][$encoded][$compressed])) {
            return $this->cache['decode'][$encoded][$compressed];
        }

        $encodedOriginal = $encoded;

        if ($compressed) {
            $encoded = substr($this->convertBase($encoded, $this->compressAlphabet, self::ALPHABET_BINARY), 1);
        }

        $decoded = '';
        $length = strlen($encoded);
        $i = 0;

        while ($i < $length) {
            [$inc, $value] = $this->getBestValue($encoded, $i);
            $decoded .= $value;
            $i += $inc;
        }

        $this->cache['decode'][$encodedOriginal][$compressed] = $decoded;

        return $decoded;
    }

    /**
     * @param string $decoded
     * @param bool   $compress
     *
     * @return string
     */
    public function encode(string $decoded, bool $compress = false): string
    {
        if ($decoded === '') {
            return '';
        }

        if (isset($this->cache['encode'][$decoded][$compress])) {
            return $this->cache['encode'][$decoded][$compress];
        }

        $encoded = '';
        $length = strlen($decoded);
        $i = 0;

        while ($i < $length) {
            [$inc, $binary] = $this->getBestBinary($decoded, $i);
            $encoded .= $binary;
            $i += $inc;
        }

        if ($compress) {
            return $this->convertBase('1' . $encoded, self::ALPHABET_BINARY, $this->compressAlphabet);
        }

        $this->cache['encode'][$decoded][$compress] = $encoded;

        return $encoded;
    }

    /**
     * @param string $num
     * @param string $fromBaseAlphabet
     * @param string $toBaseAlphabet
     *
     * @return string
     */
    private function convertBase(string $num, string $fromBaseAlphabet, string $toBaseAlphabet): string
    {
        $fromBaseAlphabetLength = strlen($fromBaseAlphabet);
        $toBaseAlphabetLength = strlen($toBaseAlphabet);
        $fromBaseAlphabetFlipped = array_flip(str_split($fromBaseAlphabet));

        if (BIG_NUMBERS_LIB_INSTALLED === BIG_NUMBERS_LIB_GMP) {
            $decimal = gmp_init(0, 10);

            foreach (str_split($num) as $char) {
                $decimal = gmp_add(gmp_mul($fromBaseAlphabetLength, $decimal), (string) $fromBaseAlphabetFlipped[$char]);
            }

            if (gmp_cmp($decimal, $toBaseAlphabetLength) < 0) {
                return $toBaseAlphabet[gmp_intval($decimal)];
            }

            $result = '';
            while (gmp_cmp($decimal, 0) !== 0) {
                $result = $toBaseAlphabet[gmp_intval(gmp_mod($decimal, $toBaseAlphabetLength))] . $result;
                $decimal = gmp_div($decimal, (string) $toBaseAlphabetLength);
            }

            return $result;
        }

        if (BIG_NUMBERS_LIB_INSTALLED === BIG_NUMBERS_LIB_BCMATH) {
            $decimal = '0';

            foreach (str_split($num) as $char) {
                $decimal = bcadd(bcmul((string) $fromBaseAlphabetLength, $decimal), (string) $fromBaseAlphabetFlipped[$char]);
            }

            if (bccomp($decimal, (string) $toBaseAlphabetLength) < 0) {
                return $toBaseAlphabet[(int) $decimal];
            }

            $result = '';
            while (bccomp($decimal, '0') !== 0) {
                $result = $toBaseAlphabet[(int) bcmod($decimal, (string) $toBaseAlphabetLength)] . $result;
                $decimal = bcdiv($decimal, (string) $toBaseAlphabetLength);
            }

            return $result;
        }

        throw new RuntimeException('No library for big numbers (gmp or bcmath) loaded!');
    }

    /**
     * @param string $decoded
     * @param int    $index
     *
     * @return array
     */
    private function getBestBinary(string $decoded, int $index): array
    {
        foreach ($this->dictionary->getValues($decoded[$index]) as $key => $value) {
            $key = (string) $key;

            if (strpos($decoded, $key, $index) === $index) {
                return [strlen($key), $value];
            }
        }

        throw new RuntimeException(sprintf('Unknown key for "%s"', substr($decoded, $index)));
    }

    /**
     * @param string $encoded
     * @param int    $index
     *
     * @return array
     */
    private function getBestValue(string $encoded, int $index): array
    {
        foreach ($this->dictionary->getValuesReversed(substr($encoded, $index, $this->dictionary->getMinBinaryLength())) as $key => $value) {
            if (strpos($encoded, $value, $index) === $index) {
                return [strlen($value), $key];
            }
        }

        throw new RuntimeException(sprintf('Unknown key for "%s"', substr($encoded, $index)));
    }
}
