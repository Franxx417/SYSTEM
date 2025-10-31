<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master seeder that calls all individual seeders in correct order.
 * Safe to run repeatedly; seeders use upserts/guards to avoid duplicates.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleTypesSeeder::class,
            StatusesSeeder::class,
            UsersAndLoginSeeder::class,
            RolesSeeder::class,
            SuppliersSeeder::class,
            PurchaseOrdersSeeder::class,
            SettingsSeeder::class,
            ConstantsSeeder::class,
            // Enhanced seeders for testing with large datasets
            // EnhancedSuppliersSeeder::class,
            // EnhancedUsersSeeder::class,
            // EnhancedItemsSeeder::class,
            // EnhancedPurchaseOrdersSeeder::class,
        ]);
    }
}
