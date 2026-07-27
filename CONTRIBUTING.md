# Contributing to Cornerstone

Cornerstone is both a maintained starter kit and the source exported into new applications. Changes must account for repository maintenance and downstream application behavior separately.

## Prepare the repository

Install the project and its development tooling:

```shell
composer setup
```

Configure and migrate a local database when working on application behavior. The automated test suite uses its own in-memory SQLite database.

## Follow the coding standards

All changes must follow [CODING_STANDARDS.md](CODING_STANDARDS.md). Those standards intentionally ship with Cornerstone and continue to apply in generated applications.

Use the application quality gate for code that ships downstream:

```shell
composer fix
composer verify
```

Review formatter changes before running verification.

## Separate maintenance tests

Tests under `tests/Unit`, `tests/Feature`, and `tests/Browser` are exported into generated applications. They must enforce application behavior or standards that remain useful downstream.

Tests that protect Cornerstone packaging, release behavior, Composer lifecycle scripts, or other repository-only concerns belong under `tests/Maintenance`. Run them with the maintenance-only PHPUnit configuration:

```shell
vendor/bin/pest --configuration phpunit.maintenance.xml --compact
```

The maintenance test directory and configuration are excluded from project exports. The dedicated maintenance workflow runs them for repository pushes and pull requests.

## Preserve the project-creation lifecycle

The Composer scripts have distinct responsibilities:

- `post-create-project-cmd` performs the one-time update of `cornerstone-support`, publishes the initial stubs without `--force`, and delegates shared preparation to `composer setup`.
- `composer setup` is the canonical repeatable setup flow for dependencies, environment preparation, optional interactive Boost installation, browser tooling, and frontend assets.
- Non-interactive setup must complete every unattended step and explain how a user can finish Boost installation interactively.
- `post-update-cmd` may update existing Boost resources, but it must not require Boost to have been installed.
- No lifecycle other than initial project creation may automatically update `cornerstone-support`.

Published stubs may be customized by downstream applications. Never overwrite them automatically after project creation. Updating existing stubs requires the user to explicitly run `php artisan cornerstone:stubs --force`.

## Review project exports

Repository-only files are marked `export-ignore` in `.gitattributes`. Before a release, inspect the archive that will become the new application:

```shell
git archive --format=tar HEAD | tar -tf -
```

Confirm that maintenance tests, maintenance configuration, contributor documentation, and repository-only workflows are absent while application tests and standards remain.

The canonical expedition example is downstream application content, not repository maintenance scaffolding. Keep its implementation and tests in exports until the project deliberately replaces or removes it through the provided removal workflow.
