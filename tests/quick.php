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
        'gamma',
        'and',
        'or',
    ]
);

$dict = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS);
$huff = new Huffman($dict);

for ($i = 1; $i <= count($values); $i++) {
    for ($j = 0; $j < count($values); $j++) {
        $value = implode('', array_slice($values, $j, $i));

        $encoded = $huff->encode($value, true);
        $decoded = $huff->decode($encoded, true);

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

echo PHP_EOL;

