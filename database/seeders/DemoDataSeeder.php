<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StoreCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Store categories
        $storeCategories = [
            ['slug' => 'general', 'label' => 'General Retail', 'license_field_schema' => [['key' => 'trade_license', 'label' => 'Trade License No.', 'type' => 'text', 'required' => false]]],
            ['slug' => 'food', 'label' => 'Food / Grocery', 'license_field_schema' => [['key' => 'fssai', 'label' => 'FSSAI License No.', 'type' => 'text', 'required' => true]]],
            ['slug' => 'pharmacy', 'label' => 'Pharmacy', 'license_field_schema' => [['key' => 'drug_license', 'label' => 'Drug License No.', 'type' => 'text', 'required' => true]]],
            ['slug' => 'cattle_feed', 'label' => 'Cattle Feed', 'license_field_schema' => [['key' => 'fssai', 'label' => 'FSSAI No.', 'type' => 'text', 'required' => false], ['key' => 'feed_license', 'label' => 'Feed License No.', 'type' => 'text', 'required' => false]]],
            ['slug' => 'electronics', 'label' => 'Electronics', 'license_field_schema' => [['key' => 'trade_license', 'label' => 'Trade License No.', 'type' => 'text', 'required' => false]]],
        ];
        foreach ($storeCategories as $sc) {
            StoreCategory::firstOrCreate(['slug' => $sc['slug']], $sc);
        }

        // Units
        $units = [['name' => 'Piece', 'symbol' => 'pcs'], ['name' => 'Kilogram', 'symbol' => 'kg'], ['name' => 'Gram', 'symbol' => 'g'], ['name' => 'Litre', 'symbol' => 'L'], ['name' => 'Bag', 'symbol' => 'bag'], ['name' => 'Packet', 'symbol' => 'pkt'], ['name' => 'Box', 'symbol' => 'box'], ['name' => 'Dozen', 'symbol' => 'dz']];
        foreach ($units as $u) Unit::firstOrCreate(['symbol' => $u['symbol']], $u);

        // Product categories
        $cats = ['Grains & Pulses', 'Beverages', 'Snacks', 'Dairy', 'Personal Care', 'Electronics', 'Cattle Feed'];
        foreach ($cats as $c) Category::firstOrCreate(['name' => $c], ['name' => $c, 'slug' => \Illuminate\Support\Str::slug($c)]);

        $grains = Category::where('name', 'Grains & Pulses')->first();
        $bev = Category::where('name', 'Beverages')->first();
        $pcs = Unit::where('symbol', 'pcs')->first();
        $kg = Unit::where('symbol', 'kg')->first();

        // Demo products
        $products = [
            ['name' => 'Basmati Rice 5kg', 'sku' => 'RICE001', 'purchase_price' => 350, 'sale_price' => 420, 'gst_rate' => 5, 'stock_qty' => 100, 'low_stock_threshold' => 10, 'category_id' => $grains?->id, 'unit_id' => $kg?->id, 'hsn_code' => '1006'],
            ['name' => 'Toor Dal 1kg', 'sku' => 'DAL001', 'purchase_price' => 90, 'sale_price' => 110, 'gst_rate' => 5, 'stock_qty' => 200, 'low_stock_threshold' => 20, 'category_id' => $grains?->id, 'unit_id' => $kg?->id, 'hsn_code' => '0713'],
            ['name' => 'Bisleri Water 1L', 'sku' => 'WTR001', 'purchase_price' => 12, 'sale_price' => 20, 'gst_rate' => 18, 'stock_qty' => 500, 'low_stock_threshold' => 50, 'category_id' => $bev?->id, 'unit_id' => $pcs?->id],
            ['name' => 'Colgate Toothpaste', 'sku' => 'TOOTH001', 'purchase_price' => 55, 'sale_price' => 70, 'gst_rate' => 12, 'stock_qty' => 60, 'low_stock_threshold' => 10, 'unit_id' => $pcs?->id],
            ['name' => 'Parle-G Biscuit', 'sku' => 'BISC001', 'purchase_price' => 5, 'sale_price' => 10, 'gst_rate' => 12, 'stock_qty' => 8, 'low_stock_threshold' => 10, 'unit_id' => $pcs?->id],
        ];

        $adminId = \App\Models\User::first()?->id ?? 1;

        foreach ($products as $p) {
            $product = Product::firstOrCreate(['sku' => $p['sku']], $p);
            if ($product->wasRecentlyCreated) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'change_qty' => $p['stock_qty'],
                    'balance_qty' => $p['stock_qty'],
                    'type' => 'purchase',
                    'note' => 'Demo initial stock',
                    'created_by' => $adminId,
                    'created_at' => now(),
                ]);
            }
        }

        // Demo customers
        $customers = [
            ['name' => 'Ramesh Kumar', 'mobile' => '9876543210', 'credit_limit' => 5000, 'billing_cycle' => 'monthly'],
            ['name' => 'Suresh Traders', 'mobile' => '9876543211', 'credit_limit' => 10000, 'billing_cycle' => 'weekly'],
            ['name' => 'Meena Store', 'mobile' => '9876543212', 'credit_limit' => 2000, 'billing_cycle' => 'none'],
        ];

        foreach ($customers as $cData) {
            $customer = Customer::firstOrCreate(['mobile' => $cData['mobile']], $cData);

            // Add opening balance for Ramesh
            if ($customer->wasRecentlyCreated && $cData['name'] === 'Ramesh Kumar') {
                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'type' => 'debit',
                    'source' => 'opening',
                    'source_id' => null,
                    'amount' => 1500,
                    'balance_after' => 1500,
                    'narration' => 'Opening balance',
                    'created_by' => $adminId,
                    'created_at' => now()->subDays(30),
                ]);
                LedgerEntry::create([
                    'customer_id' => $customer->id,
                    'type' => 'credit',
                    'source' => 'payment',
                    'source_id' => null,
                    'amount' => 500,
                    'balance_after' => 1000,
                    'narration' => 'Partial payment',
                    'created_by' => $adminId,
                    'created_at' => now()->subDays(15),
                ]);
            }
        }
    }
}
