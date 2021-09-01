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

namespace Mordilion\HuffmanPHP\Dictionary;

/**
 * @author Henning Huncke <mordilion@gmx.de>
 */
final class Occurrence
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var array<string|int, string>
     */
    private $data;

    /**
     * @var int
     */
    private $depth;

    private function __construct(int $count, array $data, int $depth)
    {
        $this->count = $count;
        $this->data = $data;
        $this->depth = $depth;
    }

    public static function createInitialized(int $count, string $value): self
    {
        return new self($count, [$value => ''], 0);
    }

    public static function createFromOccurrences(self $occurrence1, self $occurrence2): self
    {
        return new self(
            $occurrence1->getCount() + $occurrence2->getCount(),
            array_merge($occurrence1->getData(), $occurrence2->getData()),
            max($occurrence1->getDepth(), $occurrence2->getDepth()) + 1
        );
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
    }

    /**
     * @param int|string $value
     */
    public function setValue($value, string $binary): void
    {
        $this->data[$value] = $binary;
    }
}
