<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateSalesAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate sales_amount for categories and brands by dividing evenly among product categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sales amount recalculation...');
        
        // Reset all sales_amount to 0
        $this->info('Resetting sales_amount to 0...');
        Category::query()->update(['sales_amount' => 0]);
        Brand::query()->update(['sales_amount' => 0]);
        $this->info('✅ Reset completed');
        
        // Get all non-cancelled orders
        $this->info('Processing orders...');
        $orders = Order::where('delivery_status', '!=', 'cancelled')
            ->with('orderDetails.product.categories', 'orderDetails.product.brand')
            ->get();
        
        $totalOrders = $orders->count();
        $bar = $this->output->createProgressBar($totalOrders);
        $bar->start();
        
        $categorySales = [];
        $brandSales = [];
        
        foreach ($orders as $order) {
            foreach ($order->orderDetails as $orderDetail) {
                $product = $orderDetail->product;
                if (!$product) {
                    continue;
                }
                
                $categories = $product->categories;
                $categoryCount = $categories->count();
                
                // Calculate amount per category (divide evenly)
                if ($categoryCount > 0) {
                    $amountPerCategory = $orderDetail->total / $categoryCount;
                    foreach ($categories as $category) {
                        if (!isset($categorySales[$category->id])) {
                            $categorySales[$category->id] = 0;
                        }
                        $categorySales[$category->id] += $amountPerCategory;
                    }
                }
                
                // Brand (only one brand per product, so no division needed)
                $brand = $product->brand;
                if ($brand) {
                    if (!isset($brandSales[$brand->id])) {
                        $brandSales[$brand->id] = 0;
                    }
                    $brandSales[$brand->id] += $orderDetail->total;
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        // Update categories
        $this->info('Updating categories...');
        $categoryBar = $this->output->createProgressBar(count($categorySales));
        $categoryBar->start();
        
        foreach ($categorySales as $categoryId => $amount) {
            Category::where('id', $categoryId)->update(['sales_amount' => $amount]);
            $categoryBar->advance();
        }
        
        $categoryBar->finish();
        $this->newLine();
        $this->info('✅ Categories updated: ' . count($categorySales));
        
        // Update brands
        $this->info('Updating brands...');
        $brandBar = $this->output->createProgressBar(count($brandSales));
        $brandBar->start();
        
        foreach ($brandSales as $brandId => $amount) {
            Brand::where('id', $brandId)->update(['sales_amount' => $amount]);
            $brandBar->advance();
        }
        
        $brandBar->finish();
        $this->newLine();
        $this->info('✅ Brands updated: ' . count($brandSales));
        
        // Clear cache
        $this->info('Clearing cache...');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        
        $this->info('✅ Recalculation completed successfully!');
        $this->info('Total orders processed: ' . $totalOrders);
        $this->info('Categories updated: ' . count($categorySales));
        $this->info('Brands updated: ' . count($brandSales));
        
        return 0;
    }
}

