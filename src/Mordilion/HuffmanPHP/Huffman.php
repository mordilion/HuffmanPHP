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
            $encoded = $this->convertBase($encoded, self::BASE_MAX, self::BASE_BINARY);
        }

        $decoded = '';
        $length = strlen($encoded);

        for ($i = 0; $i < $length; $i += $count) {
            $key = false;
            $count = 0;

            while ($key === false && $count <= $length - $i) {
                $binary = substr($encoded, $i, ++$count);
                $key = $this->dictionary->getValue($binary);
            }

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
            return $this->convertBase($encoded, self::BASE_BINARY, self::BASE_MAX);
        }

        return $encoded;
    }

    /**
     * @param string $input
     * @param string $inputBase
     * @param string $outputBase
     *
     * @return string
     */
    private function convertBase(string $input, string $inputBase, string $outputBase): string
    {
        $converted = '';
        $inputBaseLength = strlen($inputBase);
        $outputBaseLength = strlen($outputBase);
        $length = strlen($input);
        $numbers = [];
        $leadingZeros = 0;

        for ($i = 0; $i < $length; $i++) {
            $position = strpos($inputBase, $input[$i]);

            if ($position === false) {
                throw new RuntimeException('Input does not match the base');
            }

            $numbers[$i] = $position;
            $leadingZeros += $leadingZeros >= $i && $position === 0 ? 1 : 0;
        }

        do {
            $divide = 0;
            $newLength = 0;

            for ($i = 0; $i < $length; $i++) {
                $divide = ($divide * $inputBaseLength) + $numbers[$i];

                if ($divide >= $outputBaseLength) {
                    $numbers[$newLength++] = (int) ($divide / $outputBaseLength);
                    $divide %= $outputBaseLength;
                    continue;
                }

                if ($newLength > 0) {
                    $numbers[$newLength++] = 0;
                }
            }

            $length = $newLength;
            $converted = $outputBase[$divide] . $converted;
        } while ($newLength > 0);

        while ($leadingZeros > 0) {
            $converted = $outputBase[0] . $converted;
            $leadingZeros--;
        }

        return $converted;
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
            $maxLength = strlen($decoded) - $index;
            $values = $this->dictionary->getValues();

            foreach ($values as $key => $value) {
                $key = (string) $key;
                $i = strlen($key);
                $substr = substr($decoded, $index, $i);

                if ($substr === $key) {
                    return [$i, $value];
                }
            }
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
