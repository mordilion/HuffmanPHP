# Huffman-PHP
A library which implements the Huffman-Algorithm to compress strings. In addition, it provides the functionality to compress the result binary string into a URL-Safe string or into a custom defined String-Base. 

## Typical Usage
```php
<?php

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

$text = 'This is a Text to compress with the Huffman-Algorithm'; // 53 chars
 
$dictionary = new Dictionary([$text]);
$huffman = new Huffman($dictionary);

// result: 10000010101000011011010000110110010101110000101100010111001111010100110001001010100100001100100101100110011011000010010011001010111100101101101100000100000000011000111001001011111111110111011110010001101000100010011001011001
echo $huffman->encode($text, false);

// result: 3QGgsnulxJqC2QweIz-V6SWj~pYoqYfA005HCR
// length: 38 chars
echo $huffman->encode($text, true);

// result: mtxhycztntclyrustzsjioonyevmiijcdwrxqflkwsymxtsb
// length: 48 chars
$huffman = new Huffman($dictionary, 'abcdefghijklmnopqrstuvwxyz');
echo $huffman->encode($text, true);

```


## Usage of MAX_LENGTH_WHOLE_WORDS to keep the Dict small and the compression high
```php
<?php

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

$text = 'A Text with multiple Text Segments, to demonstrate the compression with multiple Text Segments'; // 94 chars
 
$dictionary = new Dictionary(array_merge(
    array_unique(explode(' ', $text)),
    [' ']
), Dictionary::MAX_LENGTH_WHOLE_WORDS);
$huffman = new Huffman($dictionary);

// result: 0101011010001100110110010011010001100010110000011111011110011101011001101100100110100011100
echo $huffman->encode($text, false);

// result: 27thCP8gJKOciDUU
// length: 16 chars
echo $huffman->encode($text, true);

// result: eijxrqetkuoldtduacye
// length: 20 chars
$huffman = new Huffman($dictionary, 'abcdefghijklmnopqrstuvwxyz');
echo $huffman->encode($text, true);

```
