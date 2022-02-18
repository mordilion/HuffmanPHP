<?php

declare(strict_types=1);

namespace Mordilion\HuffmanPHP;

trait StableSortable
{
    public function uasort(array &$array, callable $callback): bool
    {
        if (version_compare(phpversion(), '8.0.0', '>=')) {
            return uasort($array, $callback);
        }

        $arrayAndPosition = $this->getArrayWithPosition($array);

        $result = usort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['value'], $b['value']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_combine(array_column($arrayAndPosition, 'key'), array_column($arrayAndPosition, 'value'));

        return $result;
    }

    public function uksort(array &$array, callable $callback): bool
    {
        if (version_compare(phpversion(), '8.0.0', '>=')) {
            return uksort($array, $callback);
        }

        $arrayAndPosition = $this->getArrayWithPosition($array);

        $result = usort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['key'], $b['key']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_combine(array_column($arrayAndPosition, 'key'), array_column($arrayAndPosition, 'value'));
    
        return $result;
    }

    public function usort(array &$array, callable $callback): bool
    {
        if (version_compare(phpversion(), '8.0.0', '>=')) {
            return usort($array, $callback);
        }

        $arrayAndPosition = $this->getArrayWithPosition($array);

        $result = usort($arrayAndPosition, function($a, $b) use($callback) {
            return $callback($a['value'], $b['value']) ?: $a['position'] <=> $b['position'];
        });

        $array = array_column($arrayAndPosition, 'value');

        return $result;
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
