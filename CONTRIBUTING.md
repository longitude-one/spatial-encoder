# How to contribute to spatial-encoder

This library is open to contributions.
It comes with a set of quality tools to help you to maintain the quality of the code and pass the CI's tests.

## How to run the quality tools

```bash
# install library-dependencies if not already done
composer install

# install quality tools
composer update-tools

# launch quality checks
composer quality
```

In this repository we use PHP_CodeSniffer (phpcs), PHP CS Fixer, PHP Mess Detector (phpmd), and PHPStan to enforce and improve code quality:

* **PHP_CodeSniffer** enforces the project's coding standards and flags style violations and suspicious constructs;
* **PHP CS Fixer** automatically reformats source code to the configured style rules while preserving behavior;
* **PHP Mess Detector** detects potential bugs, dead or unused code, and maintainability issues such as high cyclomatic complexity;
* **PHPStan** performs advanced static analysis with type inference to surface type-related errors, incorrect API usage, and contract violations before runtime.

## How to run the tests

```bash
# Run all test
composer coverage
```

## Composer commands

Prepare the PHP and Markdown tools before running quality checks:

```bash
composer install
composer update-tools
npm install --ignore-scripts
```

`composer update-tools` updates only the four isolated PHP quality environments.
Use `composer update` separately to update the library dependencies. Composer
lockfiles remain local and ignored.

| Command | Purpose |
| --- | --- |
| `composer test` | Run PHPUnit without coverage |
| `composer coverage` | Generate coverage reports |
| `composer coverage-check` | Check the coverage threshold |
| `composer cs-check` | Check formatting without changing files |
| `composer cs-fix` | Apply formatting corrections |
| `composer phpstan` | Run static analysis |
| `composer phpmd` | Analyze production code and tests |
| `composer phpcs` | Check coding standards |
| `composer markdown` | Check Markdown |
| `composer quality` | Check PHP and Markdown without changing source files |
| `composer update-tools` | Update PHP quality tools |
| `composer audit-dependencies` | Audit PHP dependencies |
| `composer check` | Run all checks |

No minimum coverage percentage is configured; the coverage check requires a
valid, non-empty report.

## Continuous integration

Pull requests run the supported PHP combinations and a separate Markdown check
when relevant files change. Superseded pull request runs are cancelled.
The `CI` check summarizes the relevant jobs, including documentation-only changes.

CI uses `composer coverage-ci` with `phpunit.ci.xml` to generate only
`build/coverage/clover.xml` for Codecov. No HTML/text coverage reports or coverage
artifacts are produced. `composer coverage` keeps the local reports unchanged.
After relevant changes reach `main`, a lightweight workflow updates Codecov
without repeating the PHP quality checks or compatibility matrix.

Weekly runs resolve current dependencies and collect the complete matrix results.
Composer and npm caches contain downloads; lockfiles remain local and ignored.
Matrix jobs without coverage disable the coverage driver. They ignore only the
optional development requirement `ext-pcov` during dependency installation.
