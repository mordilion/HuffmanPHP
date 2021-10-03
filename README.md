# huffman-php
## Usage
```php
<?php

use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

$text = 'This is a Text to compress with the Huffman-Algorythem';

// Dictionary where each array element will be one dictionary entry 
$dictionary = new Dictionary([$text], Dictionary::MAX_LENGTH_WHOLE_WORDS);

// Dictionary with max 1 character length
$dictionary = new Dictionary([$text], 1);

// Dictionary with 1-2 character length
$dictionary = new Dictionary([$text], 2);

$huffman = new Huffman($dictionary);

echo $huffman->encode($text, false); // just the huffman binary code
echo $huffman->encode($text, true); // compressed with the internal base conversion - Slow but far more compressed and URL-Safe

```
