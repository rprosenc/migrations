# 25th Migrations

This module allows to upgrade the database after source code changes by applying sql files in the database.

# Installation

Just install via composer

TODO

If you want an executable file somewhere in your project structure and not somewhere in vendors, composer will offer
you the possibility to define a bin-dir where all executabls are symlinked.

```
{
    "config": {
        "bin-dir": "scripts"
    }
}
```

# Configuration

Migrations is configured via ZF1's application.ini which is loaded from `/application/configs/application.ini`.

Currently only Doctrine's DBAL configuration is used.

During runtime a APPLICATION_ENV must be set!

# Usage

## migrations status

`migrations status` will show you a list of all unapplied changes.

## migrations apply

`migrations apply next` will apply the next changeset.

`migrations apply all` will apply all missing changesets.

`migrations apply <sql-file-name>` will apply the changeset <sql-file-name>.

With the `--only-mark` option you can apply the migration without executing it.

# Development

Running Tests:
```
# Unit tests
vendor/bin/phpunit tests/unit/

# Component tests
docker-compose up -d
vendor/bin/phpunit tests/component/
```
