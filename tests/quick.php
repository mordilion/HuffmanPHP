<?php

declare(strict_types=1);

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

require_once __DIR__ . '/../vendor/autoload.php';
$values = array_merge(
    str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_'), // fallback solution
    [
        'alpha',
        'beta',
        'cesar',
        'delta',
        'and',
        'or',
        '-'
    ],
);

$dict = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS);
$huff = new Huffman($dict);
$times = [
    'encoding' => [],
    'decoding' => [],
];

$count = 0;
$totalStart = microtime(true);

for ($i = 1; $i <= count($values); $i++) {
    for ($j = 0; $j < count($values); $j++) {
        $count++;
        $value = implode('', array_slice($values, $j, $i));

        $start = microtime(true);
        $encoded = $huff->encode($value, true);
        $times['encoding'][] = microtime(true) - $start;

        $start = microtime(true);
        $decoded = $huff->decode($encoded, true);
        $times['decoding'][] = microtime(true) - $start;

        if ($decoded !== $value) {
            echo PHP_EOL;
            echo 'Original: (' . strlen($value) . ') ' . $value . PHP_EOL;
            echo 'Encoded: (' . strlen($encoded) . ') ' . $encoded . PHP_EOL;
            echo 'Decoded: (' . strlen($decoded) . ') ' . $decoded . PHP_EOL;
            echo PHP_EOL;
        }
    }

    echo '.';
}

function getMedian(array $array): float
{
    sort($array);
    $count = count($array);
    $middleValue = floor(($count - 1) / 2);

    if ($count % 2) {
        return $array[$middleValue];
    }

    $lowMiddle = $array[$middleValue];
    $highMiddle = $array[$middleValue + 1];

    return (($lowMiddle + $highMiddle) / 2);
}

echo PHP_EOL;
echo 'Total: ' . $count . ' (' . (microtime(true) - $totalStart) . ')' . PHP_EOL;
echo PHP_EOL;
echo 'Encoding' . PHP_EOL;
echo '    AVG Time: ' . (array_sum($times['encoding']) / count($times['encoding'])) . PHP_EOL;
echo '    Min Time: ' . (min($times['encoding'])) . PHP_EOL;
echo '    Max Time: ' . (max($times['encoding'])) . PHP_EOL;
echo '    Median Time: ' . (getMedian($times['encoding'])) . PHP_EOL;
echo 'Decoding' . PHP_EOL;
echo '    AVG Time: ' . (array_sum($times['decoding']) / count($times['decoding'])) . PHP_EOL;
echo '    Min Time: ' . (min($times['decoding'])) . PHP_EOL;
echo '    Max Time: ' . (max($times['decoding'])) . PHP_EOL;
echo '    Median Time: ' . (getMedian($times['decoding'])) . PHP_EOL;
echo memory_get_usage(true) . PHP_EOL;

