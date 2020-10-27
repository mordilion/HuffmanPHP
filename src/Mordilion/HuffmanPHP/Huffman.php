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
    private const BASE_MAX = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_~';
    private const BASE_DECIMAL = '0123456789';
    private const BASE_BINARY = '01';

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
            $encoded = substr($this->convertBase($encoded, self::BASE_MAX, self::BASE_BINARY), 1);
        }

        $decoded = '';
        $length = strlen($encoded);

        for ($i = 0; $i < $length; $i++) {
            $key = false;
            $count = 0;

            while ($key === false && $count <= $length - $i) {
                $binary = substr($encoded, $i, ++$count);
                $key = $this->dictionary->getValue($binary);
            }

            $i += $count - 1;
            $decoded .= $key !== false ? $key : '';
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
            return $this->convertBase('1'. $encoded, self::BASE_BINARY, self::BASE_MAX);
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
        if ($outputAlphabet === self::BASE_DECIMAL) {
            $inputAlphabetLength = (string) strlen($inputAlphabet);
            $inputLength = strlen($input);
            $result = (string) strpos($inputAlphabet, $input[0]);

            for ($i = 1; $i < $inputLength; $i++) {
                $result = bcadd(bcmul($inputAlphabetLength, $result), (string) strpos($inputAlphabet, $input[$i]));
            }

            return $result;
        }

        $decimal = $input;
        $outputAlphabetCharacters = str_split($outputAlphabet, 1);

        if ($inputAlphabet !== self::BASE_DECIMAL) {
            $decimal = $this->convertBase($input, $inputAlphabet, self::BASE_DECIMAL);
        }

        if ($decimal < strlen($outputAlphabet)) {
            return $outputAlphabetCharacters[$decimal];
        }

        $result = '';
        $outputAlphabetLength = (string) strlen($outputAlphabet);

        while ($decimal !== '0') {
            $result = $outputAlphabetCharacters[bcmod($decimal, $outputAlphabetLength)] . $result;
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
