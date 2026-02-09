# Database Migration Guide - Phinx

##  Quick Start

### For New Team Members:

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Run migrations**
   ```bash
   docker compose run --rm php vendor/bin/phinx migrate
   ```

3. **Check result**
    - Open phpMyAdmin: `http://localhost:8080`
    - You should see `users` and `roles` tables

---

##  Daily Workflow

### After pulling changes:

```bash
git pull
composer install                                        # Install any new dependencies
docker compose run --rm php vendor/bin/phinx migrate   # Apply new migrations
```

---

## ! How to Create a New Migration

### Step 1: Create migration file

```bash
docker compose run --rm php vendor/bin/phinx create CreateYourTableName
```

**This creates:** `app/db/migrations/YYYYMMDDHHMMSS_create_your_table_name.php`

### Step 2: Edit the file

Open the created file and add your table structure:

```php
public function change(): void
{
    $table = $this->table('your_table', ['id' => false, 'primary_key' => 'your_id']);
    
    $table->addColumn('your_id', 'integer', ['identity' => true])
          ->addColumn('name', 'string', ['limit' => 255])
          ->addColumn('description', 'text', ['null' => true])
          ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
          ->addIndex(['name'])
          ->create();
}
```

### Step 3: Test locally

```bash
docker compose run --rm php vendor/bin/phinx migrate
```

### Step 4: Verify in phpMyAdmin

- Check table was created correctly
- Verify columns, types, and constraints

### Step 5: Commit and push

```bash
git add app/db/migrations/YYYYMMDDHHMMSS_create_your_table_name.php
git commit -m "Add your_table migration"
git push
```

---

## ! How to Modify an Existing Table

**❌ NEVER edit an already applied migration!**

**✅ ALWAYS create a NEW migration**

### Adding a Column

```bash
docker compose run --rm php vendor/bin/phinx create AddPhoneToUsers
```

```php
public function change(): void
{
    $table = $this->table('users');
    $table->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'after' => 'email'])
          ->update();
}
```

### Removing a Column

```bash
docker compose run --rm php vendor/bin/phinx create RemoveAgeFromUsers
```

```php
public function change(): void
{
    $table = $this->table('users');
    $table->removeColumn('age')
          ->update();
}
```

### Adding a Foreign Key

```bash
docker compose run --rm php vendor/bin/phinx create AddForeignKeyToOrders
```

```php
public function change(): void
{
    $table = $this->table('orders');
    $table->addForeignKey('user_id', 'users', 'user_id', 
                          ['delete' => 'CASCADE', 'update' => 'CASCADE'])
          ->update();
}
```

---

## ! Naming Conventions

### File naming:
```
YYYYMMDDHHMMSS_verb_description.php
```

**Phinx adds timestamp automatically**, you only provide the description:

```bash
# Good examples:
phinx create CreateOrdersTable
phinx create AddStatusToOrders
phinx create RemoveDescriptionFromProducts
phinx create CreateCartItemsTable

# Bad examples:
phinx create UpdateTable           # ❌ Not descriptive
phinx create NewTable              # ❌ What table?
phinx create create-orders-table  # ❌ Use CamelCase
```

### Class naming:
- Use **CamelCase**
- Start with verb: Create, Add, Remove, Change
- Be descriptive

---

## ! Common Column Types

```php
->addColumn('name', 'string', ['limit' => 255])              # VARCHAR(255)
->addColumn('age', 'integer')                                # INT
->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2])  # DECIMAL(10,2)
->addColumn('is_active', 'boolean', ['default' => true])     # TINYINT(1)
->addColumn('created_at', 'datetime')                        # DATETIME
->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])  # TIMESTAMP
->addColumn('description', 'text')                           # TEXT
->addColumn('status', 'enum', ['values' => ['active', 'inactive']])  # ENUM
```

---

## ! Foreign Keys

```php
->addForeignKey('column_in_this_table', 'referenced_table', 'column_in_referenced_table', 
                ['delete' => 'CASCADE', 'update' => 'CASCADE'])
```

**Options:**
- `delete`: `CASCADE`, `SET_NULL`, `RESTRICT`, `NO_ACTION`
- `update`: `CASCADE`, `SET_NULL`, `RESTRICT`, `NO_ACTION`

**Example:**
```php
// users.role_id → roles.role_id
->addForeignKey('role_id', 'roles', 'role_id', 
                ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
```

---

## ! Indexes

```php
// Regular index
->addIndex(['email'])

// Unique index
->addIndex(['email'], ['unique' => true])

// Composite index
->addIndex(['tour_date', 'language'])
```

---

## ⚠️ Common Issues

### "Table already exists"

**Solution:** Drop the table in phpMyAdmin or via SQL:
```sql
DROP TABLE IF EXISTS table_name;
```

Then run migrations again.

### "Foreign key constraint fails"

**Problem:** Trying to create FK to a table that doesn't exist yet.

**Solution:** Create tables in correct order (parent table first, then child).

### "Command not found: phinx"

**Solution:** You forgot to run `composer install`

---

## ! Rollback (Undo Migration)

```bash
# Rollback last migration
docker compose run --rm php vendor/bin/phinx rollback

# Rollback to specific version
docker compose run --rm php vendor/bin/phinx rollback -t 20260209173657

# Rollback all migrations
docker compose run --rm php vendor/bin/phinx rollback -t 0
```

---

## ! File Structure

```
Haarlem-Festival/
├── app/
│   ├── db/
│   │   ├── migrations/          ← Your migration files
│   │   │   ├── 20260209173657_create_roles_table.php
│   │   │   └── 20260209180233_create_users_table.php
│   │   └── seeds/               ← Seed files (optional)
│   ├── composer.json            ← Phinx dependency listed here
│   └── phinx.php                ← Phinx configuration
└── docker-compose.yml
```

---

## ! Rules & Best Practices

### DO:


✅ **Test migrations locally before committing**

✅ **Use descriptive names**
   ```
   Good: CreateHistoryToursTable
   Bad:  NewTable
   ```

✅ **Create new migration for changes**

✅ **Check phpMyAdmin after migration**

### DON'T:

❌ **Never edit already applied migrations**

❌ **Never delete migration files**

❌ **Never commit vendor/ folder**

❌ **Never manually edit `phinxlog` table**

---

## ! Getting Help

### Phinx Documentation:
https://book.cakephp.org/phinx/0/en/

### Questions?
Ask in team chat or check this guide!

---

## 📝 Quick Reference

### Essential Commands:

```bash
# Install dependencies
composer install

# Create migration
docker compose run --rm php vendor/bin/phinx create MigrationName

# Run migrations
docker compose run --rm php vendor/bin/phinx migrate

# Rollback last migration
docker compose run --rm php vendor/bin/phinx rollback

# Check migration status
docker compose run --rm php vendor/bin/phinx status
```

---

**Last updated:** 2026-02-09  
**Maintained by:** IT2C Group 1