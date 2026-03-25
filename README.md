# HuffmanPHP

A PHP library for string compression using the Huffman coding algorithm with URL-safe base conversion.

[![CI](https://github.com/mordilion/HuffmanPHP/actions/workflows/ci.yml/badge.svg)](https://github.com/mordilion/HuffmanPHP/actions/workflows/ci.yml) [![PHP](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg)](https://php.net/) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![Packagist](https://img.shields.io/packagist/v/mordilion/huffman-php.svg)](https://packagist.org/packages/mordilion/huffman-php)

## Features

- Huffman coding compression for strings
- Character-level and whole-word compression modes
- URL-safe output using configurable alphabets (Base10 to Base65)
- Automatic GMP/BCMath detection for base conversion
- Built-in result caching for repeated operations
- PHP 8.0+ with strict typing

## Requirements

- PHP >= 8.0
- One of these extensions for compressed output:
  - `gmp` (recommended, faster)
  - `bcmath` (fallback)

## Installation

```bash
composer require mordilion/huffman-php
```

## Usage

### Basic Encoding

Character-level encoding compresses individual characters based on their frequency. The default output uses Base65 (URL-safe alphabet) when compression is enabled.

```php
use Mordilion\HuffmanPHP\Dictionary;
use Mordilion\HuffmanPHP\Huffman;

// Build a dictionary from sample text
$text = 'This is a Text to compress with the Huffman-Algorithm'; // 53 chars

$dictionary = new Dictionary([$text]);
$huffman = new Huffman($dictionary);

// Encode to binary
$binary = $huffman->encode($text);
// => '10000010101000011011010000110110010101110000101100010111001111010100110001001010100100001100100101100110011011000010010011001010111100101101101100000100000000011000111001001011111111110111011110010001101000100010011001011001' (289 chars)

// Encode with compression (URL-safe Base65, default)
$compressed = $huffman->encode($text, true);
// => '3QGgsnulxJqC2QweIz-V6SWj~pYoqYfA005HCR' (38 chars from 53)

// Decode
$decoded = $huffman->decode($binary);           // original text
$decoded = $huffman->decode($compressed, true);  // original text
```

### Custom Alphabets

Use different character sets to encode compressed output. Smaller alphabets produce longer output but may be more suitable for restricted character sets.

```php
$huffman = new Huffman($dictionary, Huffman::ALPHABET_BASE26_LOWER);

$compressed = $huffman->encode($text, true);
// => 'mtxhycztntclyrustzsjioonyevmiijcdwrxqflkwsymxtsb' (48 chars)

$decoded = $huffman->decode($compressed, true); // original text
```

### Whole-Word Mode

Treat each input array element as an atomic token instead of breaking it into individual characters. This produces superior compression when the input has repeating words or phrases.

```php
$text = 'A Text with multiple Text Segments, to demonstrate the compression with multiple Text Segments'; // 94 chars

// Split into words and add space as a separate token
$tokens = array_merge(array_unique(explode(' ', $text)), [' ']);

$dictionary = new Dictionary($tokens, Dictionary::MAX_LENGTH_WHOLE_WORDS);
$huffman = new Huffman($dictionary);

$compressed = $huffman->encode($text, true);
// => '27thCP8gJKOciDUU' (16 chars from 94)

$decoded = $huffman->decode($compressed, true); // original text
```

## API Reference

### Dictionary

**`__construct(array $values, int $maxLength = 1)`**

Builds a Huffman dictionary from sample values.

- `$values` — Array of strings to learn character/word frequencies from
- `$maxLength` — Maximum substring length to consider. `1` = single characters (default), `Dictionary::MAX_LENGTH_WHOLE_WORDS` (0) = treat each value as a whole word

Throws `InvalidArgumentException` if `$maxLength` is negative.

**`getBinary(string $key): ?string`**

Returns the binary Huffman code for a given key, or `null` if not found.

**`getValue(string $binary): string|int|false`**

Reverse lookup: returns the original key for a binary code, or `false` if not found.

**`getMaxLength(): int`**

Returns the `maxLength` used during construction.

**`setValues(array $values): void`**

Replaces the dictionary mappings. Useful for serializing/deserializing dictionaries.

### Huffman

**`__construct(Dictionary $dictionary, string $compressAlphabet = Huffman::ALPHABET_BASE65)`**

Creates an encoder/decoder instance.

- `$dictionary` — The Huffman dictionary to use
- `$compressAlphabet` — Alphabet for compressed output (default: URL-safe Base65)

**`encode(string $decoded, bool $compress = false): string`**

Encodes a string using the Huffman dictionary.

- `$compress = false` — Returns raw binary string
- `$compress = true` — Returns base-converted string using the configured alphabet

Throws `RuntimeException` if:
- The input contains characters not in the dictionary
- Compression is enabled but neither GMP nor BCMath is available

**`decode(string $encoded, bool $compressed = false): string`**

Decodes a previously encoded string. The `$compressed` flag must match the `$compress` flag used during encoding.

Throws `RuntimeException` if the binary code is not recognized in the dictionary.

## Alphabet Constants

Alphabets are used to convert the binary Huffman codes into text. Larger alphabets produce shorter output for the same binary data.

| Constant | Characters | Size |
|---|---|---|
| `ALPHABET_BASE10` | `0-9` | 10 |
| `ALPHABET_BASE16` | `0-9A-F` | 16 |
| `ALPHABET_BASE16_LOWER` | `0-9a-f` | 16 |
| `ALPHABET_BASE26` | `A-Z` | 26 |
| `ALPHABET_BASE26_LOWER` | `a-z` | 26 |
| `ALPHABET_BASE36` | `0-9A-Z` | 36 |
| `ALPHABET_BASE36_LOWER` | `0-9a-z` | 36 |
| `ALPHABET_BASE62` | `0-9A-Za-z` | 62 |
| `ALPHABET_BASE65` | `0-9A-Za-z-_~` | 65 |

**Note:** Larger alphabets produce shorter output. Base65 is the default and uses only URL-safe characters (digits, letters, hyphen, underscore, tilde).

## Error Handling

- `InvalidArgumentException` — Thrown by `Dictionary` when `$maxLength` is negative
- `RuntimeException` — Thrown by `Huffman::encode()` when the input contains characters not in the dictionary
- `RuntimeException` — Thrown by `Huffman::encode()/decode()` with `$compress = true` when neither GMP nor BCMath is available

## Testing

```bash
composer install
vendor/bin/phpunit
vendor/bin/psalm
```

## License

MIT — see [LICENSE](LICENSE) for details.
