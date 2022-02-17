<?php

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

trait StableSortable
{
    public function uasort(array &$array, callable $callback)
    {
        $arrayAndPosition = $this->getArrayWithPosition($array);

        uasort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['value'], $b['value']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_column($arrayAndPosition, 'value');
    }

    public function uksort(array &$array, callable $callback)
    {
        $arrayAndPosition = $this->getArrayWithPosition($array);

        usort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['key'], $b['key']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_combine(array_column($arrayAndPosition, 'key'), array_column($arrayAndPosition, 'value'));
    }

    public function usort(array &$array, callable $callback)
    {
        $arrayAndPosition = $this->getArrayWithPosition($array);

        usort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['value'], $b['value']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_column($arrayAndPosition, 'value');
    }

    private function getArrayWithPosition(array $array): array
    {
        $arrayAndPosition = [];
        $position = 0;

        foreach ($array as $key => $value) {
            $arrayAndPosition[] = ['key' => $key, 'value' => $value, 'position' => $position++];
        }

        return $arrayAndPosition;
    }
}
