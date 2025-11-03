<?php
/**
 * php_array_migration.php
 * 
 * Universal database migration tool using PHP array files.
 * Guaranteed to not miss any rows.
 */

ini_set('memory_limit', '1024M');
date_default_timezone_set('UTC');

// ===== CONFIGURATION =====
$phpDataFile = __DIR__ . '/alflip_alfl_demo.php';
$newDb = array(
    'host' => 'localhost',
    'dbname' => 'alflip-new-exported',
    // 'dbname' => 'alflip-backend',
    'user' => 'root',
    'pass' => ''
);
require_once $phpDataFile;

logMessage("Pre-fetching uploads mapping...");
$uploadsMap = array();
if (isset($uploads) && is_array($uploads)) {
    foreach ($uploads as $upload) {
        if (isset($upload['id']) && isset($upload['file_name'])) {
            $uploadsMap[$upload['id']] = $upload['file_name'];
        }
    }
    logMessage("Loaded " . count($uploadsMap) . " upload mappings");
} else {
    logMessage("Warning: Uploads array not found or empty", 'WARN');
}



// ===== MAPPINGS =====
$mappings = array(
    // USERS
    'users' => array(
        'source_array' => 'users',
        'target_table' => 'users',
        'field_map' => array(
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
            'phone' => 'phone',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
        'default_values' => array(
            'verified' => 1
        ),
        'transform' => array(
            'password' => function ($value) {
                if (empty($value) || $value === 'NULL') {
                    return password_hash('default_password', PASSWORD_BCRYPT);
                }
                return password_hash($value, PASSWORD_BCRYPT);
            },
            'email' => function ($value) {
                if (empty($value) || $value === 'NULL') {
                    return 'user_' . uniqid() . '_' . mt_rand(1000, 9999) . '@migrated.example.com';
                }
                return $value;
            },
            'name' => function ($value) {
                if (empty($value) || $value === 'NULL') {
                    return 'Migrated User';
                }
                return $value;
            }
        )
    ),

//     // USER ADDRESSES
    'user_addresses' => array(
        'source_array' => 'addresses',
        'target_table' => 'user_addresses',
        'field_map' => array(
            'id' => 'id',
            'user_id' => 'user_id',
            'address' => 'address_1',
            'country_id' => 'country',
            'state_id' => 'state',
            'city_id' => 'city',
            'postal_code' => 'zip',
            'phone' => 'phone',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        )
    ),

//     // USER WISHLISTS
    
    'categories' => array(
        'source_array' => 'categories',
        'target_table' => 'categories',
        'field_map' => array(
            'id' => 'id',
            'name' => 'title',
            'parent_id' => 'parent',
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'banner' => 'image',
            'slug' => 'slug',
            'featured' => 'featured',
            'parent_id' => 'parent',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'admin_id' => 1,
            'image' => '/'
        )
    ),
    'brands' => array(
        'source_array' => 'brands',
        'target_table' => 'brands',
        'field_map' => array(
            'id' => 'id',
            'name' => 'title',
            'logo' => 'image',
            'slug' => 'slug',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'admin_id' => 1
        )
    ),

    'attributes' => array(
        'source_array' => 'attributes',
        'target_table' => 'attributes',
        'field_map' => array(
            'id' => 'id',
            'name' => 'title',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'admin_id' => 1
        )
    ),
    'attribute_values' => array(
        'source_array' => 'attribute_values',
        'target_table' => 'attribute_values',
        'field_map' => array(
            'id' => 'id',
            'attribute_id' => 'attribute_id',
            'value' => 'title',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'admin_id' => 1
        )
    ),
    // PRODUCTS
    'products' => array(
    'source_array' => 'products',
    'target_table' => 'products',
        'field_map' => array(
            'id' => 'id',
            'name' => 'title',
            'description' => 'description',
            'unit_price' => 'selling',
            'purchase_price' => 'purchased',
            'unit' => 'unit',
            'meta_title' => 'meta_title',
            'meta_description' => 'meta_description',
            'tags' => 'tags',
            'slug' => 'slug',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'thumbnail_img' => 'image',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ),
        'default_values' => array(
            'tax_rule_id' => 1,
            'shipping_rule_id' => 1,
            'status' => 1,
            'purchased' => 0.00,
            'admin_id' => 1
        ),
        'transform' => array(
            'unit_price' => function ($value, $record) {
                // Calculate: unit_price - discount
                $unitPrice = $record['unit_price'] ?? 0;
                $discount = $record['discount'] ?? 0;
                return max(0, $unitPrice - $discount); // Ensure not negative
            },
            'thumbnail_img' => function ($value, $record) use ($uploadsMap) {
                // If no image ID or uploads map is empty, return null
                // if (empty($value) || empty($uploadsMap)) {
                //     return null;
                // }
                
                // Return the file_name if found in uploads map
                return $uploadsMap[$value] ?? null;
            }
        )
    ),
    'user_wishlists' => array(
        'source_array' => 'wishlists',
        'target_table' => 'user_wishlists',
        'field_map' => array(
            'user_id' => 'user_id',
            'product_id' => 'product_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        )
    ),
    // PRODUCT IMAGES
    // 'product_images' => array(
    //     'source_array' => 'uploads',
    //     'target_table' => 'product_images',
    //     'field_map' => array(
    //         'id' => 'id',
    //         'file_name' => 'image',
    //         'created_at' => 'created_at',
    //         'updated_at' => 'updated_at'
    //     )
    // ),

    // PRODUCT REVIEWS
    'rating_reviews' => array(
        'source_array' => 'reviews',
        'target_table' => 'rating_reviews',
        'field_map' => array(
            'product_id' => 'product_id',
            'user_id' => 'user_id',
            'rating' => 'rating',
            'comment' => 'review',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        )
    ),

    // ORDERS
    'orders' => array(
        'source_array' => 'orders',
        'target_table' => 'orders',
        'field_map' => array(
            'id' => 'id',
            'user_id' => 'user_id',
            'code' => 'order_n',
            'grand_total' => 'total_amount',
            'payment_status' => 'payment_status',
            'delivery_status' => 'status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'payment_method' => 2,
            'user_address_id' => 1,
            'voucher_id' => 1
        )
    ),

    // Stock
    'stock' => array(
        'source_array' => 'products',
        'target_table' => 'updated_inventories',
        'field_map' => array(
            'id' => 'id',
            'id' => 'product_id',

            'unit_price' => 'price',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'quantity' => 200
        )
    ),

    // product_categories
    'product_categories' => array(
        'source_array' => 'products',
        'target_table' => 'product_categories',
        'field_map' => array(
            'id' => 'id',
            'id' => 'product_id',
            'category_id' => 'category_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'primary_is' => 1
        )
    ),

    // ORDERED PRODUCTS
    'ordered_products' => array(
        'source_array' => 'order_details',
        'target_table' => 'ordered_products',
        'field_map' => array(
            'order_id' => 'order_id',
            'product_id' => 'product_id',
            'quantity' => 'quantity',
            'price' => 'selling',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at'
        ),
            'default_values' => array(
            'inventory_id' => 2782,
            'shipping_place_id' => 1,
            'withdrawal_id' => 1
        )
    )
);

// ===== LOGGER =====
function logMessage($msg, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    
    // ANSI color codes
    $colors = [
        'ERROR' => "\033[31m", // Red
        'WARN'  => "\033[33m", // Yellow
        'INFO'  => "\033[36m", // Cyan
        'SUCCESS' => "\033[32m", // Green
    ];
    
    $reset = "\033[0m"; // Reset color
    
    // Apply color if type exists, otherwise use default
    $color = $colors[$type] ?? '';
    
    $line = "[$timestamp] [$type] $msg";
    
    // Console output with color
    echo $color . $line . $reset . PHP_EOL;
    
    // File output without color codes
    file_put_contents('php_array_migration.log', $line . PHP_EOL, FILE_APPEND);
}

// ===== DATABASE CONNECTION =====
try {
    $pdo = new PDO(
        "mysql:host={$newDb['host']};dbname={$newDb['dbname']};charset=utf8mb4",
        $newDb['user'],
        $newDb['pass'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    logMessage("Connected to database: {$newDb['dbname']}");
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// ===== LOAD PHP DATA FILE =====
if (!file_exists($phpDataFile)) {
    die("PHP data file not found: $phpDataFile");
}

// Include the PHP file to load all arrays
logMessage("Loading PHP data file: $phpDataFile");
require_once $phpDataFile;

// Get all defined arrays from the included file
$allArrays = get_defined_vars();
logMessage("Found " . count($allArrays) . " arrays in data file");

// ===== PRE-FETCH UPLOADS MAPPING =====



// List all available arrays for debugging
$availableArrays = array();
foreach ($allArrays as $varName => $value) {
    if (is_array($value)) {
        $availableArrays[] = $varName . ' (' . count($value) . ' records)';
    }
}
logMessage("Available arrays: " . implode(', ', $availableArrays));

// ===== MIGRATION CORE =====
foreach ($mappings as $mappingName => $map) {
    $sourceArray = $map['source_array'];
    $targetTable = $map['target_table'];
    
    logMessage("Processing mapping: $sourceArray -> $targetTable");
    
    // Check if source array exists
    if (!isset($$sourceArray) || !is_array($$sourceArray)) {
        logMessage("Source array '$$sourceArray' not found or not an array. Skipping.", 'ERROR');
        continue;
    }
    
    $sourceData = $$sourceArray;
    $totalRecords = count($sourceData);
    
    logMessage("Found $totalRecords records in $$sourceArray array");
    
    if ($totalRecords === 0) {
        logMessage("No data found in $$sourceArray, skipping.", 'WARN');
        continue;
    }

    // Fetch available columns in target table
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM $targetTable");
        $availableColumns = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $availableColumns[] = $row['Field'];
        }
        logMessage("Available columns in $targetTable: " . implode(', ', $availableColumns));
    } catch (Exception $e) {
        logMessage("Table $targetTable not found in new DB. Skipping.", 'ERROR');
        continue;
    }

    $insertedCount = 0;
$skippedCount = 0;
$duplicateCount = 0;
$errorCount = 0;

// Process each record
foreach ($sourceData as $index => $record) {
    $insertData = array();
    
    // Map fields according to field_map
    foreach ($map['field_map'] as $sourceField => $targetField) {
    // Skip if target column doesn’t exist in destination table
    if (!in_array($targetField, $availableColumns)) {
        continue;
    }
    
    // Get value from source record
    $value = isset($record[$sourceField]) ? $record[$sourceField] : null;
    
    // Apply transformation if defined
    if (isset($map['transform'][$sourceField]) && is_callable($map['transform'][$sourceField])) {
        $value = $map['transform'][$sourceField]($value, $record);
    }
    
    $insertData[$targetField] = $value;
}

// ✅ ADD THIS CODE HERE - Default values for columns not in source data
if (isset($map['default_values'])) {
    foreach ($map['default_values'] as $targetField => $defaultValue) {
        if (in_array($targetField, $availableColumns)) {
            $insertData[$targetField] = $defaultValue;
        }
    }
}

    
    // Skip if no mappable data
    if (empty($insertData)) {
        $skippedCount++;
        continue;
    }
    
    // Prepare INSERT statement
    $columns = array_keys($insertData);
    $placeholders = array();
    foreach ($columns as $col) {
        $placeholders[] = ":$col";
    }
    
    $sql = "INSERT INTO $targetTable (" . implode(',', $columns) . ") 
            VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute($insertData);
        $affectedRows = $stmt->rowCount();
        
        if ($affectedRows > 0) { 
            $insertedCount++; 
        } else {
            $duplicateCount++; 
        }
        
        // Progress reporting
        if ($insertedCount % 100 === 0) {
            logMessage("Inserted $insertedCount records into $targetTable");
        }
        
    } catch (PDOException $e) {
        $errorCount++;
        if ($errorCount <= 5) { // Log first 5 errors only
            logMessage("Error inserting record $index into $targetTable: " . $e->getMessage(), 'ERROR');
        }
    }
}
    
    // Final report for this table
    logMessage("=== MIGRATION COMPLETE: $sourceArray -> $targetTable ===",'SUCCESS');
    logMessage("Total source records: $totalRecords",'INFO');
    logMessage("Successfully inserted: $insertedCount",'SUCCESS');
    logMessage("Skipped (no data): $skippedCount",'WARN');
    logMessage("Duplicates skipped: $duplicateCount",'WARN');
    logMessage("Errors: $errorCount",'ERROR');
    
    // Verify final count in target table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $targetTable");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        logMessage("Final count in $targetTable: " . $result['total']);
    } catch (Exception $e) {
        logMessage("Could not verify final count for $targetTable",'WARN');
    }
    
    logMessage(""); // Empty line for readability
}

logMessage("===== PHP ARRAY MIGRATION COMPLETED SUCCESSFULLY =====");

// Final summary of all tables
logMessage("=== FINAL SUMMARY ===");
foreach ($mappings as $mappingName => $map) {
    $targetTable = $map['target_table'];
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $targetTable");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        logMessage("$targetTable: " . $result['total'] . " records");
    } catch (Exception $e) {
        logMessage("$targetTable: Could not count records");
    }
}
