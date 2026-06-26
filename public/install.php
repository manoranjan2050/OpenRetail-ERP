<?php
/**
 * OpenRetail ERP — Web Installer
 * Works on Hostinger shared hosting, AMPPS, cPanel, any PHP 8.1+ host
 */

define('INSTALLER_VERSION', '1.0.0');
define('BASE_PATH', dirname(__DIR__));
define('LOCK_FILE', BASE_PATH . '/storage/installed.lock');
define('ENV_FILE', BASE_PATH . '/.env');
define('ENV_EXAMPLE', BASE_PATH . '/.env.example');

// If already installed, block access
if (file_exists(LOCK_FILE) && !isset($_GET['force'])) {
    die('<!DOCTYPE html><html><head><title>Already Installed</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,sans-serif;background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4f46e5 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}.box{background:#fff;border-radius:20px;padding:48px;text-align:center;max-width:400px;box-shadow:0 25px 50px rgba(0,0,0,.3)}h1{font-size:24px;color:#1e1b4b;margin-bottom:12px}p{color:#6b7280;margin-bottom:24px}.btn{display:inline-block;padding:12px 24px;background:#4f46e5;color:#fff;border-radius:10px;text-decoration:none;font-weight:600}</style></head><body><div class="box"><div style="font-size:60px;margin-bottom:16px">✅</div><h1>Already Installed</h1><p>OpenRetail ERP is already set up and running.</p><a href="../" class="btn">Go to App →</a></div></body></html>');
}

session_start();
$step = (int)($_POST['step'] ?? $_GET['step'] ?? 1);
$errors = [];
$success = false;

// ─── REQUIREMENTS CHECK ────────────────────────────────────────────────────
function check_requirements(): array {
    $checks = [];
    $checks['PHP >= 8.1'] = version_compare(PHP_VERSION, '8.1.0', '>=');
    foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'] as $ext) {
        $checks["ext-$ext"] = extension_loaded($ext);
    }
    $checks['storage/ writable'] = is_writable(BASE_PATH . '/storage');
    $checks['bootstrap/cache/ writable'] = is_writable(BASE_PATH . '/bootstrap/cache');
    $checks['.env writable'] = !file_exists(ENV_FILE) || is_writable(ENV_FILE) || is_writable(dirname(ENV_FILE));
    return $checks;
}

// ─── WRITE .ENV ────────────────────────────────────────────────────────────
function generate_app_key(): string {
    return 'base64:' . base64_encode(random_bytes(32));
}

function write_env(array $data): bool {
    $template = file_exists(ENV_EXAMPLE) ? file_get_contents(ENV_EXAMPLE) : '';
    if (!$template) {
        $template = <<<'ENV'
APP_NAME="OpenRetail ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
ENV;
    }

    $replacements = [
        '/^APP_KEY=.*/m'       => 'APP_KEY=' . $data['app_key'],
        '/^APP_URL=.*/m'       => 'APP_URL=' . $data['app_url'],
        '/^APP_NAME=.*/m'      => 'APP_NAME="' . addslashes($data['app_name']) . '"',
        '/^DB_HOST=.*/m'       => 'DB_HOST=' . $data['db_host'],
        '/^DB_PORT=.*/m'       => 'DB_PORT=' . $data['db_port'],
        '/^DB_DATABASE=.*/m'   => 'DB_DATABASE=' . $data['db_name'],
        '/^DB_USERNAME=.*/m'   => 'DB_USERNAME=' . $data['db_user'],
        '/^DB_PASSWORD=.*/m'   => 'DB_PASSWORD=' . $data['db_pass'],
        '/^APP_ENV=.*/m'       => 'APP_ENV=production',
        '/^APP_DEBUG=.*/m'     => 'APP_DEBUG=false',
        '/^LOG_LEVEL=.*/m'     => 'LOG_LEVEL=error',
    ];

    $content = $template;
    foreach ($replacements as $pattern => $replacement) {
        $new = preg_replace($pattern, $replacement, $content);
        $content = $new !== null ? $new : $content . "\n" . $replacement;
    }
    return (bool) file_put_contents(ENV_FILE, $content);
}

// ─── RUN SQL SCHEMA ────────────────────────────────────────────────────────
function run_schema(PDO $pdo): void {
    $sql = get_schema_sql();
    foreach (array_filter(array_map('trim', explode(';--SPLIT--', $sql))) as $stmt) {
        if (trim($stmt)) $pdo->exec($stmt);
    }
}

function run_seeds(PDO $pdo, array $admin): void {
    // Roles & Permissions (Spatie)
    $perms = ['products.view','products.create','products.edit','products.delete','products.export',
               'customers.view','customers.create','customers.edit','customers.delete',
               'invoices.view','invoices.create','invoices.void',
               'payments.record','ledger.view',
               'settings.view','settings.edit',
               'reports.view','backup.run','users.manage',
               'audit.view','stock.adjust','categories.manage','units.manage'];
    $now = date('Y-m-d H:i:s');
    foreach ($perms as $p) {
        $pdo->exec("INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES ('$p','web','$now','$now')");
    }

    $roles = ['admin','salesman','backup_operator'];
    foreach ($roles as $r) {
        $pdo->exec("INSERT IGNORE INTO roles (name, guard_name, created_at, updated_at) VALUES ('$r','web','$now','$now')");
    }

    // Admin gets all perms
    $adminRoleId = $pdo->query("SELECT id FROM roles WHERE name='admin'")->fetchColumn();
    $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allPerms as $pid) {
        $pdo->exec("INSERT IGNORE INTO role_has_permissions (permission_id, role_id) VALUES ($pid, $adminRoleId)");
    }

    // Salesman gets sales perms
    $salesPerms = ['products.view','customers.view','customers.create','invoices.view','invoices.create','payments.record','ledger.view'];
    $salesRoleId = $pdo->query("SELECT id FROM roles WHERE name='salesman'")->fetchColumn();
    foreach ($salesPerms as $sp) {
        $spid = $pdo->query("SELECT id FROM permissions WHERE name='$sp'")->fetchColumn();
        if ($spid) $pdo->exec("INSERT IGNORE INTO role_has_permissions (permission_id, role_id) VALUES ($spid, $salesRoleId)");
    }

    // Admin user
    $hash = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $name = $pdo->quote($admin['name']);
    $email = $pdo->quote($admin['email']);
    $pdo->exec("INSERT INTO users (name, email, password, is_active, email_verified_at, created_at, updated_at) VALUES ($name, $email, '$hash', 1, '$now', '$now', '$now')");
    $userId = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES ($adminRoleId, 'App\\\\Models\\\\User', $userId)");

    // Business settings
    $bizName = $pdo->quote($admin['business_name'] ?? 'My Business');
    $pdo->exec("INSERT INTO business_settings (business_name, installed, currency, timezone, invoice_prefix, invoice_next_number, show_qr_on_invoice, created_at, updated_at) VALUES ($bizName, 1, 'INR', 'Asia/Kolkata', 'INV', 1, 1, '$now', '$now')");

    // Store categories
    $cats = [
        ['general_store', 'General Store'],
        ['grocery', 'Grocery / Kirana'],
        ['pharmacy', 'Pharmacy / Medical'],
        ['cattle_feed', 'Cattle Feed / Agri'],
        ['electronics', 'Electronics / Mobile'],
    ];
    foreach ($cats as $c) {
        $slug = $c[0]; $label = $pdo->quote($c[1]);
        $pdo->exec("INSERT IGNORE INTO store_categories (slug, label, created_at, updated_at) VALUES ('$slug', $label, '$now', '$now')");
    }

    // Default units
    $units = [['Piece','pcs'],['Kilogram','kg'],['Gram','g'],['Litre','L'],['Millilitre','mL'],['Bag','bag'],['Box','box'],['Dozen','doz']];
    foreach ($units as $u) {
        $name = $pdo->quote($u[0]); $sym = $pdo->quote($u[1]);
        $pdo->exec("INSERT IGNORE INTO units (name, symbol, created_at, updated_at) VALUES ($name, $sym, '$now', '$now')");
    }

    // Migrations table entries (so artisan migrate doesn't re-run)
    $migrations = [
        '0001_01_01_000000_create_users_table',
        '0001_01_01_000001_create_cache_table',
        '0001_01_01_000002_create_jobs_table',
        '2026_06_26_040936_create_permission_tables',
        '2026_06_26_040938_create_activity_log_table',
        '2026_06_26_040939_add_event_column_to_activity_log_table',
        '2026_06_26_040940_add_batch_uuid_column_to_activity_log_table',
        '2026_06_26_050000_add_fields_to_users_table',
        '2026_06_26_060001_create_business_settings_table',
        '2026_06_26_060002_create_store_categories_table',
        '2026_06_26_060003_create_stores_table',
        '2026_06_26_060004_create_categories_table',
        '2026_06_26_060005_create_units_table',
        '2026_06_26_060006_create_products_table',
        '2026_06_26_060007_create_customers_table',
        '2026_06_26_060008_create_invoices_table',
        '2026_06_26_060009_create_invoice_items_table',
        '2026_06_26_060010_create_payments_table',
        '2026_06_26_060011_create_ledger_entries_table',
        '2026_06_26_060012_create_stock_movements_table',
    ];
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL, batch INT NOT NULL)");
    foreach ($migrations as $m) {
        $pdo->exec("INSERT IGNORE INTO migrations (migration, batch) VALUES ('$m', 1)");
    }
}

// ─── FULL SCHEMA SQL ───────────────────────────────────────────────────────
function get_schema_sql(): string {
    return <<<'SQL'
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) NULL,
  `photo` VARCHAR(255) NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL PRIMARY KEY,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(255) NOT NULL UNIQUE,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `log_name` VARCHAR(255) NULL,
  `description` TEXT NOT NULL,
  `subject_type` VARCHAR(255) NULL,
  `event` VARCHAR(255) NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  `causer_type` VARCHAR(255) NULL,
  `causer_id` BIGINT UNSIGNED NULL,
  `properties` JSON NULL,
  `batch_uuid` CHAR(36) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `business_settings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_name` VARCHAR(255) NOT NULL,
  `gstin` VARCHAR(15) NULL,
  `pan` VARCHAR(10) NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(15) NULL,
  `email` VARCHAR(255) NULL,
  `logo` VARCHAR(255) NULL,
  `currency` VARCHAR(5) NOT NULL DEFAULT 'INR',
  `timezone` VARCHAR(255) NOT NULL DEFAULT 'Asia/Kolkata',
  `invoice_prefix` VARCHAR(10) NOT NULL DEFAULT 'INV',
  `invoice_next_number` INT NOT NULL DEFAULT 1,
  `terms` TEXT NULL,
  `upi_id` TEXT NULL,
  `payee_name` VARCHAR(255) NULL,
  `bank_account` TEXT NULL,
  `ifsc` VARCHAR(15) NULL,
  `show_qr_on_invoice` TINYINT(1) NOT NULL DEFAULT 1,
  `installed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `store_categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `label` VARCHAR(255) NOT NULL,
  `license_field_schema` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `stores` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `store_category_id` BIGINT UNSIGNED NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(15) NULL,
  `license_fields` JSON NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `units` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `symbol` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `products` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) NULL UNIQUE,
  `barcode` VARCHAR(255) NULL,
  `category_id` BIGINT UNSIGNED NULL,
  `unit_id` BIGINT UNSIGNED NULL,
  `hsn_code` VARCHAR(10) NULL,
  `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `sale_price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `stock_qty` DECIMAL(10,3) NOT NULL DEFAULT 0,
  `low_stock_threshold` DECIMAL(10,3) NOT NULL DEFAULT 5,
  `expiry_date` DATE NULL,
  `batch` VARCHAR(255) NULL,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `customers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(15) NULL,
  `email` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `credit_limit` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `billing_cycle` ENUM('none','weekly','monthly') NOT NULL DEFAULT 'none',
  `statement_token` VARCHAR(64) NULL UNIQUE,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `number` VARCHAR(255) NOT NULL UNIQUE,
  `store_id` BIGINT UNSIGNED NULL,
  `customer_id` BIGINT UNSIGNED NULL,
  `salesman_id` BIGINT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `tax_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `payment_mode` ENUM('cash','upi','card','credit','mixed') NOT NULL DEFAULT 'cash',
  `status` ENUM('paid','partial','credit','void') NOT NULL DEFAULT 'paid',
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `qty` DECIMAL(10,3) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `gst_rate` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `line_tax` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `line_total` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `invoice_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `mode` ENUM('cash','upi','card','bank_transfer','other') NOT NULL DEFAULT 'cash',
  `reference` VARCHAR(255) NULL,
  `received_by` BIGINT UNSIGNED NOT NULL,
  `received_at` TIMESTAMP NOT NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `ledger_entries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('debit','credit') NOT NULL,
  `source` ENUM('invoice','payment','adjustment','opening') NOT NULL,
  `source_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `balance_after` DECIMAL(12,2) NOT NULL,
  `narration` VARCHAR(255) NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;--SPLIT--

CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `change_qty` DECIMAL(10,3) NOT NULL,
  `balance_qty` DECIMAL(10,3) NOT NULL,
  `type` ENUM('sale','purchase','adjustment','return') NOT NULL,
  `ref_id` BIGINT UNSIGNED NULL,
  `note` VARCHAR(255) NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
}

// ─── PROCESS POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)$_POST['step'];

    if ($step === 2) {
        // Save DB config to session
        $_SESSION['db'] = [
            'host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'port' => trim($_POST['db_port'] ?? '3306'),
            'name' => trim($_POST['db_name'] ?? ''),
            'user' => trim($_POST['db_user'] ?? ''),
            'pass' => $_POST['db_pass'] ?? '',
        ];
        // Test connection
        try {
            $dsn = "mysql:host={$_SESSION['db']['host']};port={$_SESSION['db']['port']};dbname={$_SESSION['db']['name']};charset=utf8mb4";
            new PDO($dsn, $_SESSION['db']['user'], $_SESSION['db']['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $step = 3;
        } catch (Exception $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
            $step = 2;
        }
    } elseif ($step === 3) {
        $_SESSION['admin'] = [
            'name'          => trim($_POST['admin_name'] ?? ''),
            'email'         => trim($_POST['admin_email'] ?? ''),
            'password'      => $_POST['admin_password'] ?? '',
            'business_name' => trim($_POST['business_name'] ?? 'My Business'),
            'app_url'       => trim($_POST['app_url'] ?? 'http://localhost'),
        ];
        $_SESSION['admin']['password_confirm'] = $_POST['admin_password_confirm'] ?? '';
        $pw = $_SESSION['admin']['password'];
        if (strlen($pw) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($pw !== $_SESSION['admin']['password_confirm']) $errors[] = 'Passwords do not match.';
        if (!filter_var($_SESSION['admin']['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid admin email.';
        if (!$_SESSION['admin']['name']) $errors[] = 'Admin name is required.';
        if (!$errors) $step = 4;
        else $step = 3;
    } elseif ($step === 4) {
        // Run installation
        try {
            $db = $_SESSION['db'];
            $admin = $_SESSION['admin'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            run_schema($pdo);
            run_seeds($pdo, $admin);

            $appKey = generate_app_key();
            write_env([
                'app_key'  => $appKey,
                'app_url'  => $admin['app_url'],
                'app_name' => $admin['business_name'],
                'db_host'  => $db['host'],
                'db_port'  => $db['port'],
                'db_name'  => $db['name'],
                'db_user'  => $db['user'],
                'db_pass'  => $db['pass'],
            ]);

            // Create lock file
            @mkdir(BASE_PATH . '/storage', 0755, true);
            file_put_contents(LOCK_FILE, date('Y-m-d H:i:s'));

            // Clear caches if possible
            @unlink(BASE_PATH . '/bootstrap/cache/config.php');
            @unlink(BASE_PATH . '/bootstrap/cache/routes-v7.php');
            @unlink(BASE_PATH . '/bootstrap/cache/services.php');

            $step = 5;
            $success = true;
            session_destroy();
        } catch (Exception $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
            $step = 4;
        }
    }
}

$reqChecks = check_requirements();
$allReqOk  = !in_array(false, $reqChecks, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OpenRetail ERP — Install</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --indigo: #4f46e5;
  --indigo-dark: #3730a3;
  --indigo-light: #6366f1;
  --green: #10b981;
  --red: #ef4444;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
}

body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.container {
  width: 100%;
  max-width: 680px;
}

/* Logo */
.logo {
  text-align: center;
  margin-bottom: 32px;
}
.logo-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--indigo-light), var(--indigo-dark));
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  margin-bottom: 12px;
  box-shadow: 0 8px 32px rgba(79,70,229,.4);
  animation: float 3s ease-in-out infinite;
}
@keyframes float {
  0%,100% { transform: translateY(0); }
  50% { transform: translateY(-6px); }
}
.logo h1 { color: #fff; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }
.logo p { color: rgba(255,255,255,.6); font-size: 14px; margin-top: 4px; }

/* Steps indicator */
.steps {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  margin-bottom: 28px;
}
.step-item {
  display: flex;
  align-items: center;
  gap: 0;
}
.step-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  transition: all .3s;
  position: relative;
  z-index: 1;
}
.step-circle.done { background: var(--green); color: #fff; }
.step-circle.active { background: var(--indigo-light); color: #fff; box-shadow: 0 0 0 4px rgba(99,102,241,.3); }
.step-circle.pending { background: rgba(255,255,255,.1); color: rgba(255,255,255,.4); border: 2px solid rgba(255,255,255,.15); }
.step-line { width: 40px; height: 2px; background: rgba(255,255,255,.15); }
.step-line.done { background: var(--green); }

/* Card */
.card {
  background: #fff;
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 25px 60px rgba(0,0,0,.4);
  animation: slideUp .4s ease both;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(20px); }
  to { opacity:1; transform:translateY(0); }
}

.card-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 6px;
}
.card-subtitle {
  font-size: 14px;
  color: var(--gray-500);
  margin-bottom: 28px;
}

/* Requirement rows */
.req-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 6px;
  font-size: 14px;
}
.req-row.ok { background: #f0fdf4; color: #166534; }
.req-row.fail { background: #fef2f2; color: #991b1b; }
.req-badge { font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.req-badge.ok { background: #dcfce7; color: #15803d; }
.req-badge.fail { background: #fee2e2; color: #dc2626; }

/* Form */
.form-group { margin-bottom: 18px; }
label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
label span.req { color: var(--red); margin-left: 2px; }
input[type=text], input[type=email], input[type=password], input[type=number], input[type=url] {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid var(--gray-200);
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: var(--gray-800);
  background: var(--gray-50);
  transition: border-color .2s, box-shadow .2s;
  outline: none;
}
input:focus {
  border-color: var(--indigo-light);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}
.hint { font-size: 12px; color: var(--gray-500); margin-top: 4px; }

/* Errors */
.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  padding: 12px 16px;
  color: #991b1b;
  font-size: 13px;
  margin-bottom: 20px;
}
.alert-error ul { margin-left: 16px; }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  border: none;
  font-family: 'Inter', sans-serif;
  text-decoration: none;
}
.btn-primary {
  background: linear-gradient(135deg, var(--indigo-light), var(--indigo-dark));
  color: #fff;
  box-shadow: 0 4px 15px rgba(79,70,229,.4);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,.5); }
.btn-primary:active { transform: translateY(0); }
.btn-secondary { background: var(--gray-100); color: var(--gray-700); }
.btn-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 15px rgba(16,185,129,.4); }
.btn-success:hover { transform: translateY(-1px); }

.btn-row { display: flex; gap: 12px; justify-content: flex-end; margin-top: 28px; align-items: center; }
.btn-row .back { color: var(--gray-500); font-size: 14px; text-decoration: none; padding: 8px; }
.btn-row .back:hover { color: var(--gray-700); }

/* Grid */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:500px){ .grid-2{ grid-template-columns:1fr; } }

/* Divider */
.divider { border: none; border-top: 1px solid var(--gray-200); margin: 24px 0; }

/* Progress bar */
.progress-wrap { margin: 20px 0; }
.progress-label { display: flex; justify-content: space-between; font-size: 13px; color: var(--gray-600); margin-bottom: 8px; }
.progress-bar { height: 8px; background: var(--gray-200); border-radius: 99px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--indigo-light), var(--green)); border-radius: 99px; transition: width 1s ease; }

/* Success */
.success-icon { font-size: 72px; text-align: center; margin-bottom: 16px; animation: bounce .6s ease; }
@keyframes bounce {
  0% { transform: scale(0); opacity:0; }
  60% { transform: scale(1.2); }
  100% { transform: scale(1); opacity:1; }
}
.info-box { background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
.info-box p { font-size: 14px; color: var(--gray-700); margin-bottom: 4px; }
.info-box strong { color: var(--gray-900); }
</style>
</head>
<body>
<div class="container">

  <div class="logo">
    <div class="logo-icon">🛒</div>
    <h1>OpenRetail ERP</h1>
    <p>v<?= INSTALLER_VERSION ?> · Web Installer</p>
  </div>

  <!-- Steps -->
  <div class="steps">
    <?php
    $stepLabels = ['Check','Database','Admin','Install','Done'];
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i < $step ? 'done' : ($i == $step ? 'active' : 'pending');
        $icon = $i < $step ? '✓' : $i;
        echo "<div class='step-item'>";
        echo "<div class='step-circle $cls'>$icon</div>";
        if ($i < 5) echo "<div class='step-line " . ($i < $step ? 'done' : '') . "'></div>";
        echo "</div>";
    }
    ?>
  </div>

  <div class="card">

    <?php if ($errors): ?>
    <div class="alert-error">
      <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
    </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <!-- STEP 1: Requirements -->
    <div class="card-title">System Requirements</div>
    <div class="card-subtitle">Make sure your server meets all requirements before proceeding.</div>

    <?php foreach ($reqChecks as $label => $ok): ?>
    <div class="req-row <?= $ok ? 'ok' : 'fail' ?>">
      <span><?= htmlspecialchars($label) ?></span>
      <span class="req-badge <?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? '✓ OK' : '✗ Missing' ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (!$allReqOk): ?>
    <p style="margin-top:16px;font-size:13px;color:var(--red);">⚠ Fix the failing requirements before proceeding. Contact your hosting provider to enable missing PHP extensions.</p>
    <?php endif; ?>

    <div class="btn-row">
      <form method="POST">
        <input type="hidden" name="step" value="2">
        <button type="submit" class="btn btn-primary" <?= !$allReqOk ? 'disabled' : '' ?>>
          Continue <span>→</span>
        </button>
      </form>
    </div>

    <?php elseif ($step === 2): ?>
    <!-- STEP 2: Database -->
    <div class="card-title">Database Configuration</div>
    <div class="card-subtitle">Enter your MySQL database credentials. Create the database first via cPanel / phpMyAdmin.</div>

    <form method="POST">
      <input type="hidden" name="step" value="2">
      <div class="grid-2">
        <div class="form-group">
          <label>DB Host <span class="req">*</span></label>
          <input type="text" name="db_host" value="<?= htmlspecialchars($_SESSION['db']['host'] ?? '127.0.0.1') ?>" required>
          <div class="hint">Usually 127.0.0.1 or localhost</div>
        </div>
        <div class="form-group">
          <label>DB Port</label>
          <input type="number" name="db_port" value="<?= htmlspecialchars($_SESSION['db']['port'] ?? '3306') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Database Name <span class="req">*</span></label>
        <input type="text" name="db_name" value="<?= htmlspecialchars($_SESSION['db']['name'] ?? '') ?>" required placeholder="openretail_erp">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>DB Username <span class="req">*</span></label>
          <input type="text" name="db_user" value="<?= htmlspecialchars($_SESSION['db']['user'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>DB Password</label>
          <input type="password" name="db_pass" value="">
          <div class="hint">Leave blank if no password</div>
        </div>
      </div>
      <div class="btn-row">
        <a href="?step=1" class="back">← Back</a>
        <button type="submit" class="btn btn-primary">Test & Continue →</button>
      </div>
    </form>

    <?php elseif ($step === 3): ?>
    <!-- STEP 3: Admin -->
    <div class="card-title">Business &amp; Admin Setup</div>
    <div class="card-subtitle">Create your admin account and set your business name.</div>

    <form method="POST">
      <input type="hidden" name="step" value="3">
      <div class="form-group">
        <label>Business Name <span class="req">*</span></label>
        <input type="text" name="business_name" value="<?= htmlspecialchars($_SESSION['admin']['business_name'] ?? 'My Business') ?>" required>
      </div>
      <div class="form-group">
        <label>Application URL <span class="req">*</span></label>
        <input type="url" name="app_url" value="<?= htmlspecialchars($_SESSION['admin']['app_url'] ?? 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>" required>
        <div class="hint">e.g. https://yourdomain.com (no trailing slash)</div>
      </div>
      <hr class="divider">
      <div class="form-group">
        <label>Admin Full Name <span class="req">*</span></label>
        <input type="text" name="admin_name" value="<?= htmlspecialchars($_SESSION['admin']['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Admin Email <span class="req">*</span></label>
        <input type="email" name="admin_email" value="<?= htmlspecialchars($_SESSION['admin']['email'] ?? '') ?>" required>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Admin Password <span class="req">*</span></label>
          <input type="password" name="admin_password" required minlength="8" id="pw1">
          <div class="hint">Minimum 8 characters</div>
        </div>
        <div class="form-group">
          <label>Confirm Password <span class="req">*</span></label>
          <input type="password" name="admin_password_confirm" required minlength="8" id="pw2">
          <div class="hint" id="pw-match" style="color:var(--red);display:none">Passwords do not match</div>
        </div>
      </div>
      <script>
      document.getElementById('pw2').addEventListener('input',function(){
        var match = this.value===document.getElementById('pw1').value;
        document.getElementById('pw-match').style.display = match||!this.value ? 'none':'block';
      });
      </script>
      <div class="btn-row">
        <a href="?step=2" class="back">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
      </div>
    </form>

    <?php elseif ($step === 4): ?>
    <!-- STEP 4: Installing -->
    <div class="card-title">Installing…</div>
    <div class="card-subtitle">Creating database tables, seeding data, writing configuration.</div>

    <div class="progress-wrap">
      <div class="progress-label"><span id="progress-label">Starting…</span><span id="progress-pct">0%</span></div>
      <div class="progress-bar"><div class="progress-fill" id="progress-fill" style="width:0%"></div></div>
    </div>

    <div id="log" style="background:var(--gray-900);color:#10b981;padding:16px;border-radius:12px;font-family:monospace;font-size:13px;height:180px;overflow-y:auto;margin-bottom:16px;">
      <div id="log-content">▶ Starting installation…</div>
    </div>

    <form method="POST" id="install-form">
      <input type="hidden" name="step" value="4">
    </form>

    <script>
    const steps = [
      [15, 'Connecting to database…'],
      [30, 'Creating tables…'],
      [55, 'Setting up roles & permissions…'],
      [70, 'Creating admin user…'],
      [85, 'Writing configuration…'],
      [95, 'Finalizing…'],
    ];
    let i = 0;
    const fill = document.getElementById('progress-fill');
    const lbl = document.getElementById('progress-label');
    const pct = document.getElementById('progress-pct');
    const log = document.getElementById('log-content');

    function tick() {
      if (i < steps.length) {
        const [p, msg] = steps[i++];
        fill.style.width = p + '%';
        lbl.textContent = msg;
        pct.textContent = p + '%';
        log.innerHTML += '\n▶ ' + msg;
        document.getElementById('log').scrollTop = 9999;
        setTimeout(tick, 600);
      } else {
        setTimeout(() => document.getElementById('install-form').submit(), 400);
      }
    }
    setTimeout(tick, 300);
    </script>

    <?php elseif ($step === 5): ?>
    <!-- STEP 5: Done -->
    <div style="text-align:center;">
      <div class="success-icon">🎉</div>
      <div class="card-title" style="text-align:center;margin-bottom:8px;">Installation Complete!</div>
      <p style="color:var(--gray-500);font-size:14px;margin-bottom:24px;">OpenRetail ERP is ready to use.</p>

      <div class="info-box" style="text-align:left;">
        <p>🔐 <strong>Security:</strong> Delete or rename <code>public/install.php</code> from your server after login.</p>
        <p style="margin-top:8px;">📁 <strong>File permissions:</strong> Ensure <code>storage/</code> is writable (chmod 775).</p>
        <p style="margin-top:8px;">🌐 <strong>Web root:</strong> Point your domain to the <code>public/</code> folder.</p>
      </div>

      <div style="margin-top:24px;">
        <a href="./" class="btn btn-success" style="justify-content:center;width:100%;font-size:16px;">
          🚀 Open OpenRetail ERP
        </a>
      </div>
    </div>

    <?php endif; ?>

  </div>

  <p style="text-align:center;color:rgba(255,255,255,.3);font-size:12px;margin-top:20px;">
    OpenRetail ERP · Open Source · PHP <?= PHP_VERSION ?>
  </p>

</div>
</body>
</html>
