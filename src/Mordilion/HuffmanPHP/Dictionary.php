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
     * @var int
     */
    private $maxLength;

    /**
     * @var int
     */
    private $minBinaryLength = PHP_INT_MAX;

    /**
     * @var array
     */
    private $occurrences = [];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var array
     */
    private $valuesByCharacter = [];

    /**
     * @var array
     */
    private $valuesReversed = [];

    /**
     * @var array
     */
    private $valuesReversedByCharacter = [];

    /**
     * @var array
     */
    private $valuesFlipped = [];

    /**
     * Dictionary constructor.
     *
     * @param array $values
     * @param int   $maxLength
     */
    public function __construct(array $values, int $maxLength = 1)
    {
        if ($maxLength < self::MAX_LENGTH_WHOLE_WORDS) {
            throw new InvalidArgumentException('Parameter $maxLength must be greater than ' . self::MAX_LENGTH_WHOLE_WORDS);
        }

        $this->maxLength = $maxLength;

        $this->calculateOccurrences($values);
        $this->buildDictionary($this->occurrences);
        $this->prepareValues();
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getBinary(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    /**
     * @param string|null $startCharacter
     *
     * @return array
     */
    public function getValues(?string $startCharacter = null): array
    {
        if ($startCharacter === null) {
            return $this->values;
        }

        return $this->valuesByCharacter[$startCharacter] ?? $this->values;
    }

    /**
     * @return array
     */
    public function getValuesReversed(?string $startCharacter = null): array
    {
        if ($startCharacter === null) {
            return $this->valuesReversed;
        }

        return $this->valuesReversedByCharacter[$startCharacter] ?? $this->valuesReversed;
    }

    /**
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    /**
     * @return int
     */
    public function getMinBinaryLength(): int
    {
        return $this->minBinaryLength;
    }

    /**
     * @param string $binary
     *
     * @return false|int|string
     */
    public function getValue(string $binary)
    {
        return $this->valuesFlipped[$binary] ?? false;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;

        $this->prepareValues();
    }

    /**
     * @param array $values
     */
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
                    $substr = substr($value, $i, $substrLength);
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
     * @param string                $value
     */
    private function buildDictionary($data, string $value = ''): void
    {
        if ($data === null) {
            return;
        }

        if (is_array($data)) {
            $this->buildDictionary($data[0]['value'] ?? null, $value . '0');
            $this->buildDictionary($data[1]['value'] ?? null, $value . '1');

            return;
        }

        $this->values[$data] = $value;
    }

    private function prepareValues(): void
    {
        $this->valuesReversed = $this->values;

        $keys = array_map('strlen', array_keys($this->values));
        array_multisort($keys, SORT_DESC, $this->values);
        $keys = array_map('strlen', $this->valuesReversed);
        array_multisort($keys, SORT_ASC, $this->valuesReversed);

        $this->minBinaryLength = PHP_INT_MAX;
        $this->valuesFlipped = array_flip($this->values);
        $this->valuesByCharacter = [];
        $this->valuesReversedByCharacter = [];

        foreach ($this->values as $key => $value) {
            $keyString = (string) $key;
            $this->valuesByCharacter[$keyString[0]][$key] = $value;
            $this->minBinaryLength = min($this->minBinaryLength, strlen($value));
        }

        foreach ($this->valuesReversed as $key => $value) {
            $this->valuesReversedByCharacter[substr($value, 0, $this->minBinaryLength)][$key] = $value;
        }
    }

    /**
     * @param array $occurrences
     */
    private function sortByCount(array &$occurrences): void
    {
        usort($occurrences, static function (array $left, array $right) {
            return (int) $left['count'] <=> (int) $right['count'];
        });
    }
}
