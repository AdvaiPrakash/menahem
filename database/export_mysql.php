<?php

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sql = "-- ============================================================\n";
$sql .= "-- MENAHEM LUXURY HOME ESSENTIALS - HOSTINGER PHPMYADMIN DUMP\n";
$sql .= '-- Generated: '.date('Y-m-d H:i:s')."\n";
$sql .= "-- Compatible with MySQL 5.7 / 8.0+ and MariaDB on Hostinger\n";
$sql .= "-- ============================================================\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql .= "SET time_zone = \"+00:00\";\n\n";

// 1. Table users
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "-- Table structure for `users`\n";
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "DROP TABLE IF EXISTS `users`;\n";
$sql .= "CREATE TABLE `users` (\n";
$sql .= "  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `email` varchar(255) NOT NULL,\n";
$sql .= "  `email_verified_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  `password` varchar(255) NOT NULL,\n";
$sql .= "  `role` varchar(50) NOT NULL DEFAULT 'viewer',\n";
$sql .= "  `remember_token` varchar(100) DEFAULT NULL,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  UNIQUE KEY `users_email_unique` (`email`),\n";
$sql .= "  KEY `users_role_index` (`role`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$users = User::all();
if ($users->isNotEmpty()) {
    $sql .= "-- Dumping data for table `users`\n";
    foreach ($users as $u) {
        $name = addslashes($u->name);
        $email = addslashes($u->email);
        $pass = addslashes($u->password);
        $role = addslashes($u->role?->value ?? 'admin');
        $created = $u->created_at ?: date('Y-m-d H:i:s');
        $updated = $u->updated_at ?: date('Y-m-d H:i:s');
        $sql .= 'INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES ';
        $sql .= "({$u->id}, '{$name}', '{$email}', '{$pass}', '{$role}', '{$created}', '{$updated}');\n";
    }
    $sql .= "\n";
}

// 2. Table categories
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "-- Table structure for `categories`\n";
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "DROP TABLE IF EXISTS `categories`;\n";
$sql .= "CREATE TABLE `categories` (\n";
$sql .= "  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `slug` varchar(255) NOT NULL,\n";
$sql .= "  `parent_id` bigint(20) unsigned DEFAULT NULL,\n";
$sql .= "  `parent_slug` varchar(255) DEFAULT NULL,\n";
$sql .= "  `path` varchar(255) DEFAULT NULL,\n";
$sql .= "  `image` varchar(500) DEFAULT NULL,\n";
$sql .= "  `description` text DEFAULT NULL,\n";
$sql .= "  `badge` varchar(50) DEFAULT NULL,\n";
$sql .= "  `sort_order` int(11) NOT NULL DEFAULT 0,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  UNIQUE KEY `categories_slug_unique` (`slug`),\n";
$sql .= "  KEY `categories_parent_slug_index` (`parent_slug`),\n";
$sql .= "  KEY `categories_path_index` (`path`),\n";
$sql .= "  KEY `categories_sort_order_index` (`sort_order`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$categories = Category::all();
if ($categories->isNotEmpty()) {
    $sql .= "-- Dumping data for table `categories`\n";
    foreach ($categories as $c) {
        $name = addslashes($c->name);
        $slug = addslashes($c->slug);
        $parentId = $c->parent_id ? $c->parent_id : 'NULL';
        $parentSlug = $c->parent_slug ? "'".addslashes($c->parent_slug)."'" : 'NULL';
        $path = $c->path ? "'".addslashes($c->path)."'" : 'NULL';
        $img = $c->image ? "'".addslashes($c->image)."'" : 'NULL';
        $desc = $c->description ? "'".addslashes($c->description)."'" : 'NULL';
        $badge = $c->badge ? "'".addslashes($c->badge)."'" : 'NULL';
        $sort = (int) $c->sort_order;
        $created = $c->created_at ?: date('Y-m-d H:i:s');
        $updated = $c->updated_at ?: date('Y-m-d H:i:s');
        $sql .= 'INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `parent_slug`, `path`, `image`, `description`, `badge`, `sort_order`, `created_at`, `updated_at`) VALUES ';
        $sql .= "({$c->id}, '{$name}', '{$slug}', {$parentId}, {$parentSlug}, {$path}, {$img}, {$desc}, {$badge}, {$sort}, '{$created}', '{$updated}');\n";
    }
    $sql .= "\n";
}

// 3. Table products
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "-- Table structure for `products`\n";
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "DROP TABLE IF EXISTS `products`;\n";
$sql .= "CREATE TABLE `products` (\n";
$sql .= "  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `category_id` bigint(20) unsigned DEFAULT NULL,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `slug` varchar(255) NOT NULL,\n";
$sql .= "  `category` varchar(255) DEFAULT NULL,\n";
$sql .= "  `category_slug` varchar(255) NOT NULL,\n";
$sql .= "  `category_slugs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_slugs`)),\n";
$sql .= "  `material` varchar(255) DEFAULT NULL,\n";
$sql .= "  `price` varchar(50) NOT NULL DEFAULT '₹0',\n";
$sql .= "  `numeric_price` int(10) unsigned NOT NULL DEFAULT 0,\n";
$sql .= "  `original_price` varchar(50) DEFAULT NULL,\n";
$sql .= "  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,\n";
$sql .= "  `reviews` int(10) unsigned NOT NULL DEFAULT 0,\n";
$sql .= "  `badge` varchar(50) DEFAULT NULL,\n";
$sql .= "  `desc` text DEFAULT NULL,\n";
$sql .= "  `image` varchar(500) NOT NULL,\n";
$sql .= "  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),\n";
$sql .= "  `in_stock` tinyint(1) NOT NULL DEFAULT 1,\n";
$sql .= "  `is_featured` tinyint(1) NOT NULL DEFAULT 0,\n";
$sql .= "  `is_bestseller` tinyint(1) NOT NULL DEFAULT 0,\n";
$sql .= "  `sort_order` int(11) NOT NULL DEFAULT 0,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  KEY `products_slug_index` (`slug`),\n";
$sql .= "  KEY `products_category_slug_index` (`category_slug`),\n";
$sql .= "  KEY `products_numeric_price_index` (`numeric_price`),\n";
$sql .= "  KEY `products_in_stock_index` (`in_stock`),\n";
$sql .= "  KEY `products_sort_order_index` (`sort_order`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$products = Product::all();
if ($products->isNotEmpty()) {
    $sql .= "-- Dumping data for table `products`\n";
    foreach ($products as $p) {
        $catId = $p->category_id ? $p->category_id : 'NULL';
        $name = addslashes($p->name);
        $slug = addslashes($p->slug);
        $cat = $p->category ? "'".addslashes($p->category)."'" : 'NULL';
        $catSlug = addslashes($p->category_slug);
        $catSlugs = $p->category_slugs ? "'".addslashes(json_encode($p->category_slugs))."'" : 'NULL';
        $material = $p->material ? "'".addslashes($p->material)."'" : 'NULL';
        $price = addslashes($p->price);
        $numPrice = (int) $p->numeric_price;
        $origPrice = $p->original_price ? "'".addslashes($p->original_price)."'" : 'NULL';
        $rating = number_format($p->rating, 2, '.', '');
        $reviews = (int) $p->reviews;
        $badge = $p->badge ? "'".addslashes($p->badge)."'" : 'NULL';
        $desc = $p->desc ? "'".addslashes($p->desc)."'" : 'NULL';
        $img = addslashes($p->image);
        $gallery = $p->gallery ? "'".addslashes(json_encode($p->gallery))."'" : 'NULL';
        $inStock = $p->in_stock ? 1 : 0;
        $featured = $p->is_featured ? 1 : 0;
        $bestseller = $p->is_bestseller ? 1 : 0;
        $sort = (int) $p->sort_order;
        $created = $p->created_at ?: date('Y-m-d H:i:s');
        $updated = $p->updated_at ?: date('Y-m-d H:i:s');

        $sql .= 'INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `category`, `category_slug`, `category_slugs`, `material`, `price`, `numeric_price`, `original_price`, `rating`, `reviews`, `badge`, `desc`, `image`, `gallery`, `in_stock`, `is_featured`, `is_bestseller`, `sort_order`, `created_at`, `updated_at`) VALUES ';
        $sql .= "({$p->id}, {$catId}, '{$name}', '{$slug}', {$cat}, '{$catSlug}', {$catSlugs}, {$material}, '{$price}', {$numPrice}, {$origPrice}, {$rating}, {$reviews}, {$badge}, {$desc}, '{$img}', {$gallery}, {$inStock}, {$featured}, {$bestseller}, {$sort}, '{$created}', '{$updated}');\n";
    }
    $sql .= "\n";
}

// 4. Table inquiries
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "-- Table structure for `inquiries`\n";
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "DROP TABLE IF EXISTS `inquiries`;\n";
$sql .= "CREATE TABLE `inquiries` (\n";
$sql .= "  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `email` varchar(255) DEFAULT NULL,\n";
$sql .= "  `phone` varchar(50) DEFAULT NULL,\n";
$sql .= "  `type` varchar(50) NOT NULL DEFAULT 'general',\n";
$sql .= "  `product_name` varchar(255) DEFAULT NULL,\n";
$sql .= "  `message` text DEFAULT NULL,\n";
$sql .= "  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),\n";
$sql .= "  `status` varchar(50) NOT NULL DEFAULT 'new',\n";
$sql .= "  `admin_notes` text DEFAULT NULL,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT NULL,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  KEY `inquiries_type_index` (`type`),\n";
$sql .= "  KEY `inquiries_status_index` (`status`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$inquiries = Inquiry::all();
if ($inquiries->isNotEmpty()) {
    $sql .= "-- Dumping data for table `inquiries`\n";
    foreach ($inquiries as $inq) {
        $name = addslashes($inq->name);
        $email = $inq->email ? "'".addslashes($inq->email)."'" : 'NULL';
        $phone = $inq->phone ? "'".addslashes($inq->phone)."'" : 'NULL';
        $type = addslashes($inq->type);
        $prodName = $inq->product_name ? "'".addslashes($inq->product_name)."'" : 'NULL';
        $msg = $inq->message ? "'".addslashes($inq->message)."'" : 'NULL';
        $details = $inq->details ? "'".addslashes(json_encode($inq->details))."'" : 'NULL';
        $status = addslashes($inq->status);
        $notes = $inq->admin_notes ? "'".addslashes($inq->admin_notes)."'" : 'NULL';
        $created = $inq->created_at ?: date('Y-m-d H:i:s');
        $updated = $inq->updated_at ?: date('Y-m-d H:i:s');

        $sql .= 'INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `type`, `product_name`, `message`, `details`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES ';
        $sql .= "({$inq->id}, '{$name}', {$email}, {$phone}, '{$type}', {$prodName}, {$msg}, {$details}, '{$status}', {$notes}, '{$created}', '{$updated}');\n";
    }
    $sql .= "\n";
}

// 5. Standard Laravel Sessions, Cache, Jobs tables
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "-- Standard Laravel Support Tables\n";
$sql .= "-- ------------------------------------------------------------\n";
$sql .= "DROP TABLE IF EXISTS `sessions`;\n";
$sql .= "CREATE TABLE `sessions` (\n";
$sql .= "  `id` varchar(255) NOT NULL,\n";
$sql .= "  `user_id` bigint(20) unsigned DEFAULT NULL,\n";
$sql .= "  `ip_address` varchar(45) DEFAULT NULL,\n";
$sql .= "  `user_agent` text DEFAULT NULL,\n";
$sql .= "  `payload` longtext NOT NULL,\n";
$sql .= "  `last_activity` int(11) NOT NULL,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  KEY `sessions_user_id_index` (`user_id`),\n";
$sql .= "  KEY `sessions_last_activity_index` (`last_activity`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

$outPath = __DIR__.'/menahem_hostinger_phpmyadmin.sql';
file_put_contents($outPath, $sql);
echo 'Export successfully written to: '.$outPath.' ('.strlen($sql)." bytes)\n";
