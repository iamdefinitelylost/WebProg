# F1 Merch Database Migrations

The project keeps a versioned copy of the core schema in `database/migrations/001_f1_merch_core.sql`.

`db.php` remains backward-compatible with existing XAMPP databases and adds missing legacy columns when necessary. For a clean installation, import `schema.sql` into MariaDB/MySQL. For an existing installation, keep the current database and allow `db.php` to perform compatibility upgrades.

## Recommended workflow
1. Back up `f1_merch` before schema changes.
2. Test migrations on a development database.
3. Record each future schema change as a new numbered migration.
4. Never commit database passwords or real customer data.
