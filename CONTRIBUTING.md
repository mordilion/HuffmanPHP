# Contributing

Thanks for considering a contribution to HuffmanPHP!

## Development Setup

```bash
git clone git@github.com:mordilion/HuffmanPHP.git
cd HuffmanPHP
composer install
```

### Required PHP Extensions

- `gmp` (recommended)
- `bcmath` (fallback)

Both extensions are needed to run the full test suite.

## Running Tests

```bash
# Unit tests
vendor/bin/phpunit

# Static analysis
vendor/bin/psalm
```

All tests must pass and Psalm must report no errors before submitting a pull request.

## Code Style

- PHP 8.0+ features are welcome (union types, named arguments, match expressions, etc.)
- Use strict typing (`declare(strict_types=1)`) in all files
- Follow PSR-4 autoloading (namespace `Mordilion\HuffmanPHP\` maps to `src/`)
- Keep methods focused and short

## Pull Request Process

1. Fork the repository and create a feature branch from `master`
2. Add or update tests for your changes
3. Ensure all tests pass and Psalm reports no errors
4. Keep commits focused — one logical change per commit
5. Open a pull request against `master` with a clear description of the change

## Reporting Issues

Open an issue on GitHub with:
- A clear description of the problem
- Steps to reproduce
- Expected vs. actual behavior
- PHP version and extensions loaded
