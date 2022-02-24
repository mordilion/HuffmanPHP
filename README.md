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
