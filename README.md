## Service Tracker STOMLC
This repository houses a WordPress plugin. If you're searching for a manual or guidance on how to use the Service Tracker STOLMC, please visit this [site](https://delbem.net/).

## For developers and WordPress plugin reviewers
Developers who wish to contribute are encouraged to fork this repo and submit a pull request. **The sections below provide clarity on this plugin's development flow.**

### PHP Development Workflow

This project uses modern PHP tooling to ensure code quality and consistency. All tools require **PHP 8.2 or higher**.

#### Installing Dependencies

```bash
# Install PHP dependencies (including dev tools)
composer install
```

#### Code Quality Tools

We use two main tools for PHP code quality:

1. **PHPCS (PHP CodeSniffer)** - Checks coding standards and style
2. **PHPStan** - Static analysis to catch bugs before they happen

**Check code quality:**
```bash
# Run all checks (PHPCS + PHPStan)
make test

# Or run individually:
make phpcs    # Check coding standards
make phpstan  # Run static analysis
```

**Auto-fix issues:**
```bash
# Auto-fix coding standard violations (what PHPCBF can fix)
make fix

# Or directly:
make phpcbf
```

#### PHPStan Configuration

PHPStan is configured at **level 4** (out of 9) for gradual adoption. The configuration file `phpstan.neon` includes:

- WordPress integration via `szepeviktor/phpstan-wordpress`
- Ignored errors for common WordPress patterns
- Baseline generation support for existing code

**To raise the strictness level** as code quality improves:
```neon
# In phpstan.neon, increase the level:
level: 6  # or 7, 8, max
```

**To generate a baseline** (useful when introducing PHPStan to legacy code):
```bash
make phpstan:baseline
```

This creates `phpstan-baseline.neon` which documents existing issues. New code must pass PHPStan without baseline exceptions.

#### Editor Configuration

The project includes `.editorconfig` to ensure consistent formatting across editors and IDEs. Most modern editors support this automatically.

**Editor settings:**
- PHP files: Tabs (size 4), LF line endings, UTF-8
- JSON/YAML/Markdown: Spaces (size 2)
- Trailing whitespace: Auto-trimmed

#### Composer Scripts

You can also use Composer scripts directly:

```bash
composer run phpcs          # Run PHPCS
composer run phpcbf         # Auto-fix with PHPCBF
composer run phpstan        # Run PHPStan
composer run phpstan:baseline  # Generate PHPStan baseline
composer run test           # Run all checks
composer run fix            # Auto-fix issues
```

### 1. Stable plugin generation
- **Automated Stable Version Generation**: This repository features a GitHub workflow that auto-generates a stable branch based on the latest main branch. Whenever code is merged into the main branch, the stable version is updated accordingly.

- **Manual Stable Version Generation**: Alternatively, you can clone this repo, run `npm install`, `composer install`, and then `npm run generate:stable`. This will produce a stable version derived from your current working branch. It will be saved as a zip file in the directory above.

### 2. WordPress code reviewers
Please note that this repo serves as the development version of what we intend to offer to WordPress users. The end-users receive a stable, compiled version without any development-related files or folders. **Given this, it's advisable to review both this development repository, which includes human-readable JS, and the stable version.**

## License

This WordPress plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html) for more details.

