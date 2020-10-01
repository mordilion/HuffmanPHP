<?php

declare(strict_types=1);

use HuffmanPHP\Dictionary;
use HuffmanPHP\Huffman;

require_once __DIR__ . '/../vendor/autoload.php';

$values = array_merge(
    str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_|'),
    [
        'ag',
        'allflat',
        'allflat_def',
        'allflat_opt',
        'allflat_sms_opt',
        'cb',
        'comflat',
        'comflat_def',
        'comflat_opt',
        'esim',
        'gg',
        'gg_omvlz',
        'gg_z',
        'ggv',
        'iflat',
        'iflat_def',
        'iflat_opt',
        'kag',
        'kvk',
        'gigacube',
        'rnpbonus',
        'rnp_total',
        'sgh_total',
        'smsflat',
        'smsflat_def',
        'smsflat_opt',
        'startpak',
        'triplesim',
        'min_sms_pak_def',
        'min_sms_pak_opt',
        'base',
        'vk',
    ]
);

$dict = new Dictionary($values, Dictionary::MAX_LENGTH_WHOLE_WORDS, 1);
$huff = new Huffman($dict);

$value = '808786|ag-allflat_def-galaxy_s10-gg_z-iflat_def-rnp_total-smsflat_def-vk|color-black_memorysize-128gb';
$encoded = $huff->encode($value, true);

echo 'Original: (' . strlen($value) . ') ' . $value . PHP_EOL;
echo 'Base64: (' . strlen(base64_encode($value)) . ') ' . base64_encode($value) . PHP_EOL;
echo 'Encoded: (' . strlen($encoded) . ') ' . $encoded . PHP_EOL;

$decoded = $huff->decode($encoded, true);

echo 'Decoded: (' . strlen($decoded) . ') ' . $decoded . PHP_EOL;

