# Drupal Quality Checker

This project sets up code quality checking tools for Drupal projects. It has been customized from [vijaycs85/drupal-quality-checker](https://github.com/vijaycs85/drupal-quality-checker).

## Features

- PHP CodeSniffer
- Drupal Coder
- Composer Normalize
- PHP Parallel Lint
- GrumPHP Shim
- PHPMD
- PHPUnit

## Installation

To install the project, run the following command:

```bash
composer require  --dev razeem/drupal-quality-checker:~1.0.2
```

### Add this to your composer.json in the respective project

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/razeem/drupal-quality-checker.git"
    }
  ]
}
```

## Usage

After installation, you can use the following commands to check code quality:

- `vendor/bin/phpcs` - Runs PHP CodeSniffer
- `vendor/bin/phpmd` - Runs PHPMD
- `vendor/bin/parallel-lint` - Runs PHP Parallel Lint
- `vendor/bin/phpunit` - Runs PHPUnit

## Customization

This project has been customized to include a different logo.

## Support

For issues, please visit the [issue tracker](https://github.com/razeem/drupal-quality-checker/issues).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
