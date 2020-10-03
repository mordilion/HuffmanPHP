<?php

declare(strict_types=1);

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

require_once __DIR__ . '/../vendor/autoload.php';

$values = array_merge(
    str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_|'),
    [
        'alpha',
        'beta',
        'cesar',
        'delta',
        'and',
        'or',
        '-'
    ]
);

$dict = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS);
$huff = new Huffman($dict);

$value = 'alpha-and-beta-or-alpha-and-cesar-or-beta-and-delta';
$encoded = $huff->encode($value, true);

echo 'Original: (' . strlen($value) . ') ' . $value . PHP_EOL;
echo 'Base64: (' . strlen(base64_encode($value)) . ') ' . base64_encode($value) . PHP_EOL;
echo 'Encoded: (' . strlen($encoded) . ') ' . $encoded . PHP_EOL;

$decoded = $huff->decode($encoded, true);

echo 'Decoded: (' . strlen($decoded) . ') ' . $decoded . PHP_EOL;

