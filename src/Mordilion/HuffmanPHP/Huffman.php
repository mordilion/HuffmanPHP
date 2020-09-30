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
    private const BASE_MAX = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_|~^';

    /**
     * @var Dictionary
     */
    private $dictionary;

    public function __construct(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
    }

    public function decode(string $encoded, bool $compressed = false): string
    {
        if ($encoded === '') {
            return '';
        }

        $decoded = '';
        $count = $this->dictionary->getMaxBinaryLength();
        $version = $this->dictionary->getVersion();
        $encodedVersion = 0;

        if ($compressed) {
            $encoded = $this->convertBase($encoded, self::BASE_MAX, '01');
        }

        if (strlen($encoded) % $count !== 0) {
            $encoded = str_pad($encoded, strlen($encoded) + ($count - (strlen($encoded) % $count)), '0', STR_PAD_LEFT);
        }

        if ($version > 0) {
            $encodedVersion = bindec(substr($encoded, 0, $count));
            $encoded = substr($encoded, $count);
        }

        $length = strlen($encoded);

        if ($encodedVersion !== $version) {
            throw new RuntimeException(sprintf('Wrong version (dict: %d, enc: %d)', $version, $encodedVersion));
        }

        for ($i = 0; $i < $length; $i += $count) {
            $binary = substr($encoded, $i, $count);
            $key = $this->dictionary->getKeyByDecimal(bindec($binary));
            $decoded .= $key;
        }

        return $decoded;
    }

    public function encode(string $decoded, bool $compress = false): string
    {
        $encoded = '';
        $length = strlen($decoded);
        $count = $this->dictionary->getMaxBinaryLength();

        for ($i = 0; $i < $length; $i++) {
            [$inc, $binary] = $this->getBestBinary($decoded, $i);
            $encoded .= str_pad($binary, $count, '0', STR_PAD_LEFT);
            $i += $inc - 1;
        }

        $version = str_pad(decbin($this->dictionary->getVersion()), $count, '0', STR_PAD_LEFT);

        if ($compress) {
            return $this->convertBase($version . $encoded, '01', self::BASE_MAX);
        }

        return $version . $encoded;
    }

    private function convertBase(string $input, string $inputBase, string $outputBase): string
    {
        $inputBaseLength = strlen($inputBase);
        $outputBaseLength = strlen($outputBase);

        $length = strlen($input);
        $result = '';
        $number = [];

        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($inputBase, $input[$i]);
        }

        do {
            $divide = 0;
            $newLength = 0;

            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $inputBaseLength + $number[$i];

                if ($divide >= $outputBaseLength) {
                    $number[$newLength++] = (int) ($divide / $outputBaseLength);
                    $divide %= $outputBaseLength;
                    continue;
                }

                if ($newLength > 0) {
                    $number[$newLength++] = 0;
                }
            }

            $length = $newLength;
            $result = $outputBase[$divide] . $result;
        } while ($newLength !== 0);

        return $result;
    }

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

            if (!empty($binary)) {
                return [$i, $binary];
            }
        }

        throw new RuntimeException(sprintf('Unknown key for "%s"', substr($decoded, $index)));
    }
}
