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
composer require razeem/drupal-quality-checker
```

### Add this to your composer.json in the respective project

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/yourusername/drupal-quality-checker"
    }
  ],
  "require": {
    "applab/drupal-quality-checker": "dev-main"
  }
}
```

## Usage

After installation, you can use the following commands to check code quality:

- `vendor/bin/phpcs` - Runs PHP CodeSniffer
- `vendor/bin/phpmd` - Runs PHPMD
- `vendor/bin/parallel-lint` - Runs PHP Parallel Lint
- `vendor/bin/phpunit` - Runs PHPUnit

## Customization

This project has been customized to include a different template file and the Applab logo.

## Adding Custom Rules

To add custom rules, copy the provided `.dist` files to the root of your project:

```bash
cp vendor/applab/drupal-quality-checker/dist/phpcs.xml.dist phpcs.xml.dist
cp vendor/applab/drupal-quality-checker/dist/phpmd.xml.dist phpmd.xml.dist
cp vendor/applab/drupal-quality-checker/dist/grumphp.yml.dist grumphp.yml.dist
```

## Support

For issues, please visit the [issue tracker](https://github.com/applab/drupal-quality-checker/issues).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
