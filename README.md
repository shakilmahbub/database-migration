# PHP Database Migration Tool

This is a **universal database migration utility** designed for developers who want to migrate data between databases using an **intermediate PHP array export**.  

It works in two simple stages:

1. **Export your existing database** into a PHP array file (e.g. `exported_data.php`).
2. **Run this migration script** to import the arrays into a new MySQL database.

This method ensures:
- **High reliability** — no rows are skipped or lost.
- **Full control** over data mappings and transformations.
- **Easy debugging and customization** during the migration process.

Use this when you need a **flexible and scriptable way** to migrate data across environments, structures, or systems — especially when direct SQL dumps are not suitable.


---

## 🚀 Features

* **Migrate from PHP arrays** directly into MySQL tables.
* **Custom field mappings** between source arrays and target tables.
* **Data transformation hooks** for processing values before insertion.
* **Default value assignment** for missing fields.
* **Upload ID-to-file mapping** support.
* **Progress and summary logging** with color-coded console output.
* **Automatic skip handling** for missing or invalid data.
* **Detailed reporting** after each migration step.

---

## 📁 File Structure

```
/project-root
│
├── php_array_migration.php   # Main migration script
├── alflip_alfl_demo.php      # PHP array data source
├── php_array_migration.log   # Auto-generated migration log
└── README.md                 # This documentation
```

---

## ⚙️ Configuration

Update the **database configuration** at the top of the script:

```php
$newDb = array(
    'host' => 'localhost',
    'dbname' => 'alflip-new-exported',
    'user' => 'root',
    'pass' => ''
);
```

Then, set the path to your data file:

```php
$phpDataFile = __DIR__ . '/alflip_alfl_demo.php';
```

---

## 🧩 How It Works

1. Loads the PHP data file (which contains multiple arrays such as `$users`, `$products`, etc.).
2. Maps each array to a target database table based on `$mappings`.
3. For each mapping:

   * Reads all records from the array.
   * Applies transformations and default values.
   * Inserts records into the database table.
4. Logs progress, errors, and a final summary.

---

## 🧠 Customization

### Field Mapping

Define which source fields map to which target columns:

```php
'field_map' => array(
    'name' => 'title',
    'email' => 'email',
    'password' => 'password'
)
```

### Default Values

Automatically set values for missing fields:

```php
'default_values' => array(
    'verified' => 1,
    'admin_id' => 1
)
```

### Transformations

Apply transformations before inserting data:

```php
'transform' => array(
    'password' => function ($value) {
        return password_hash($value ?: 'default_password', PASSWORD_BCRYPT);
    }
)
```

---

## 🧾 Logging

Every run generates a `php_array_migration.log` file containing detailed logs:

* ✅ **SUCCESS** — Successful inserts and completions
* ⚠️ **WARN** — Missing data or skipped arrays
* ❌ **ERROR** — Insert or connection failures

The console also shows color-coded progress in real time.

---

## 🖥️ Running the Script

Run the migration from your terminal:

```bash
php php_array_migration.php
```

Expected console output:

```
[2025-11-05 12:00:00] [INFO] Connected to database: alflip-new-exported
[2025-11-05 12:00:01] [INFO] Processing mapping: users -> users
[2025-11-05 12:00:02] [SUCCESS] Inserted 100 records into users
[2025-11-05 12:00:03] [SUCCESS] PHP ARRAY MIGRATION COMPLETED SUCCESSFULLY
```

---

## ✅ Requirements

* PHP 8.0+
* MySQL 5.7 or higher
* PDO extension enabled

---

## 🧰 Notes

* The script **skips invalid or missing tables** automatically.
* If an array doesn’t exist, it logs and continues (no fatal errors).
* For performance, it commits records individually — safe but slower.

---

## 🧩 Example Use Case

You have an export file `alflip_alfl_demo.php` containing arrays like `$users`, `$products`, `$orders`, etc. You can migrate them all into a new Laravel or raw MySQL database using this script.

---

## 🧾 License

This script is open for modification and reuse in your migration projects.

**Author:** Shakil ICT
**Version:** 1.0.0
**Date:** November 2025
