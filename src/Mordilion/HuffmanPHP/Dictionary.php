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

use InvalidArgumentException;

/**
 * @author Henning Huncke <mordilion@gmx.de>
 */
class Dictionary
{
    public const MAX_LENGTH_WHOLE_WORDS = 0;

    /**
     * @var array
     */
    private $dictionary = [];

    /**
     * @var int
     */
    private $maxBinaryLength = 0;

    /**
     * @var int
     */
    private $maxLength = 1;

    /**
     * @var array
     */
    private $occurrences = [];

    /**
     * @var int
     */
    private $version;

    public function __construct(array $values, int $maxLength = 1, int $version = 0)
    {
        if ($maxLength < self::MAX_LENGTH_WHOLE_WORDS) {
            throw new InvalidArgumentException('Parameter $maxLength must be greater than ' . self::MAX_LENGTH_WHOLE_WORDS);
        }

        $this->maxLength = $maxLength;

        $this->calculateOccurrences($values);
        $this->fill($this->occurrences);

        $keys = array_map('strlen', array_keys($this->dictionary));
        array_multisort($keys, SORT_DESC, $this->dictionary);

        $this->version = $version;
    }

    public function getBinary(string $key): ?string
    {
        return $this->dictionary[$key] ?? null;
    }

    public function getValues(): array
    {
        return $this->dictionary;
    }

    public function getMaxBinaryLength(): int
    {
        return $this->maxBinaryLength;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return int|string
     */
    public function getKey(string $binary)
    {
        foreach ($this->dictionary as $key => $value) {
            if (str_pad($binary, strlen($value), '0', STR_PAD_LEFT) === $value) {
                return $key;
            }
        }

        return '';
    }

    /**
     * @return int|string
     */
    public function getKeyByDecimal(int $decimal)
    {
        foreach ($this->dictionary as $key => $value) {
            if ((int) bindec($value) === $decimal) {
                return $key;
            }
        }

        return '';
    }

    private function calculateOccurrences(array $values): void
    {
        $occurrences = [];

        foreach ($values as $value) {
            if ($this->maxLength === self::MAX_LENGTH_WHOLE_WORDS) {
                $occurrences[$value] = [
                    'count' => (int) ($occurrences[$value]['count'] ?? 0) + 1,
                    'value' => $value,
                ];
            }

            $length = strlen($value);

            for ($j = $this->maxLength; $j > 0; $j--) {
                $substrLength = $j;

                for ($i = 0; $i < $length; $i++) {
                    $substr = substr($value, 0, $substrLength);
                    $occurrences[$substr] = [
                        'count' => substr_count($value, $substr),
                        'value' => $substr,
                    ];
                }
            }
        }

        ksort($occurrences);
        $this->sortByCount($occurrences);

        while (count($occurrences) > 1) {
            $row1 = array_shift($occurrences);
            $row2 = array_shift($occurrences);

            $occurrences[] = [
                'count' => (int) $row1['count'] + (int) $row2['count'],
                'value' => [$row1, $row2],
            ];

            $this->sortByCount($occurrences);
        }

        $this->occurrences = (array) (reset($occurrences)['value'] ?? []);
    }

    /**
     * @param null|array|string|int $data
     */
    private function fill($data, string $value = ''): void
    {
        $this->maxBinaryLength = max(strlen($value), $this->maxBinaryLength);

        if ($data === null) {
            return;
        }

        if (is_array($data)) {
            $this->fill($data[0]['value'] ?? null, $value . '0');
            $this->fill($data[1]['value'] ?? null, $value . '1');

            return;
        }

        $this->dictionary[$data] = $value;
    }

    private function sortByCount(array &$occurrences): void
    {
        usort($occurrences, static function (array $left, array $right) {
            return (int) $left['count'] <=> (int) $right['count'];
        });
    }
}
