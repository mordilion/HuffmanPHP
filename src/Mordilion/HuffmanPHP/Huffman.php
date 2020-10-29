<?php

/**
 * This file is part of the Mordilion\HuffmanPHP package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @copyright (c) Henning Huncke - <mordilion@gmx.de>
 */

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

use RuntimeException;

/**
 * @author Henning Huncke <mordilion@gmx.de>
 */
class Huffman
{
    private const ALPHABET_MAX = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_~';
    private const ALPHABET_BINARY = '01';

    /**
     * @var Dictionary
     */
    private $dictionary;

    /**
     * Huffman constructor.
     *
     * @param Dictionary $dictionary
     */
    public function __construct(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
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

        if ($compressed) {
            $encoded = substr($this->convertBase($encoded, self::ALPHABET_MAX, self::ALPHABET_BINARY), 1);
        }

        $decoded = '';
        $length = strlen($encoded);
        $minBinaryLength = $this->dictionary->getMinBinaryLength() - 1;

        for ($i = 0; $i < $length; $i++) {
            $key = false;
            $count = $minBinaryLength;

            while  ($key === false && $count <= $length - 1) {
                $key = $this->dictionary->getValue(substr($encoded, $i, ++$count));
            }

            $decoded .= $key;
            $i += $count - 1;
        }

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

        $encoded = '';
        $length = strlen($decoded);

        for ($i = 0; $i < $length; $i++) {
            [$inc, $binary] = $this->getBestBinary($decoded, $i);
            $encoded .= $binary;
            $i += $inc - 1;
        }

        if ($compress) {
            return $this->convertBase('1'. $encoded, self::ALPHABET_BINARY, self::ALPHABET_MAX);
        }

        return $encoded;
    }

    /**
     * @param string $input
     * @param string $inputAlphabet
     * @param string $outputAlphabet
     *
     * @return string
     */
    private function convertBase(string $input, string $inputAlphabet, string $outputAlphabet): string
    {
        $inputAlphabetLength = (string) strlen($inputAlphabet);
        $outputAlphabetLength = (string) strlen($outputAlphabet);

        $inputLength = strlen($input);
        $decimal = (string) strpos($inputAlphabet, $input[0]);

        for ($i = 1; $i < $inputLength; $i++) {
            $decimal = bcadd(bcmul($inputAlphabetLength, $decimal), (string) strpos($inputAlphabet, $input[$i]));
        }

        if ($decimal < $outputAlphabetLength) {
            return $outputAlphabet[(int) $decimal];
        }

        $result = '';

        while ($decimal !== '0') {
            $result = $outputAlphabet[(int) bcmod($decimal, $outputAlphabetLength)] . $result;
            $decimal = bcdiv($decimal, $outputAlphabetLength, 0);
        }

        return $result;
    }

    /**
     * @param string $decoded
     * @param int    $index
     *
     * @return array
     */
    private function getBestBinary(string $decoded, int $index): array
    {
        $maxLength = $this->dictionary->getMaxLength();

        if ($maxLength === Dictionary::MAX_LENGTH_WHOLE_WORDS) {
            foreach ($this->dictionary->getValues($decoded[$index]) as $key => $value) {
                $key = (string) $key;

                if (strpos($decoded, $key, $index) === $index) {
                    return [strlen($key), $value];
                }
            }

            $maxLength = strlen($decoded) - $index;
        }

        for ($i = $maxLength; $i > 0; $i--) {
            $substr = substr($decoded, $index, $i);
            $binary = $this->dictionary->getBinary($substr);

            if ($binary !== null) {
                return [$i, $binary];
            }
        }

        throw new RuntimeException(sprintf('Unknown key for "%s"', substr($decoded, $index)));
    }
}
