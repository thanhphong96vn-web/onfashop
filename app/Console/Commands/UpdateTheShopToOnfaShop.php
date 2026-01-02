<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTheShopToOnfaShop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:the-shop-to-onfa-shop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all instances of "The Shop" to "ONFA Shop" in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting update process...');
        
        $updated = 0;
        
        // Update settings table
        $this->info('Updating settings table...');
        $settingsUpdated = DB::table('settings')
            ->where('value', 'LIKE', '%The Shop%')
            ->orWhere('value', 'LIKE', '%the shop%')
            ->orWhere('value', 'LIKE', '%THE SHOP%')
            ->update([
                'value' => DB::raw("REPLACE(REPLACE(REPLACE(value, 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')")
            ]);
        $updated += $settingsUpdated;
        $this->info("Updated {$settingsUpdated} settings records");
        
        // Update pages table
        $this->info('Updating pages table...');
        $schema = DB::getSchemaBuilder();
        $pagesColumns = $schema->getColumnListing('pages');
        
        $pagesUpdateData = [
            'title' => DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(title, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')"),
            'content' => DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(content, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')")
        ];
        
        if (in_array('meta_title', $pagesColumns)) {
            $pagesUpdateData['meta_title'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(meta_title, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
        }
        if (in_array('meta_description', $pagesColumns)) {
            $pagesUpdateData['meta_description'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(meta_description, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
        }
        
        $pagesWhere = function($query) use ($pagesColumns) {
            $query->where('title', 'LIKE', '%The Shop%')
                  ->orWhere('title', 'LIKE', '%the shop%')
                  ->orWhere('title', 'LIKE', '%THE SHOP%')
                  ->orWhere('content', 'LIKE', '%The Shop%')
                  ->orWhere('content', 'LIKE', '%the shop%')
                  ->orWhere('content', 'LIKE', '%THE SHOP%');
            if (in_array('meta_title', $pagesColumns)) {
                $query->orWhere('meta_title', 'LIKE', '%The Shop%')
                      ->orWhere('meta_title', 'LIKE', '%the shop%')
                      ->orWhere('meta_title', 'LIKE', '%THE SHOP%');
            }
            if (in_array('meta_description', $pagesColumns)) {
                $query->orWhere('meta_description', 'LIKE', '%The Shop%')
                      ->orWhere('meta_description', 'LIKE', '%the shop%')
                      ->orWhere('meta_description', 'LIKE', '%THE SHOP%');
            }
        };
        
        $pagesUpdated = DB::table('pages')
            ->where($pagesWhere)
            ->update($pagesUpdateData);
        $updated += $pagesUpdated;
        $this->info("Updated {$pagesUpdated} pages records");
        
        // Update page_translations table (only has title and content columns)
        $this->info('Updating page_translations table...');
        $pageTranslationsUpdated = DB::table('page_translations')
            ->where(function($query) {
                $query->where('title', 'LIKE', '%The Shop%')
                      ->orWhere('title', 'LIKE', '%the shop%')
                      ->orWhere('title', 'LIKE', '%THE SHOP%')
                      ->orWhere('content', 'LIKE', '%The Shop%')
                      ->orWhere('content', 'LIKE', '%the shop%')
                      ->orWhere('content', 'LIKE', '%THE SHOP%');
            })
            ->update([
                'title' => DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(title, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')"),
                'content' => DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(content, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')")
            ]);
        $updated += $pageTranslationsUpdated;
        $this->info("Updated {$pageTranslationsUpdated} page_translations records");
        
        // Update blogs table if exists
        if (DB::getSchemaBuilder()->hasTable('blogs')) {
            $this->info('Updating blogs table...');
            $schema = DB::getSchemaBuilder();
            $blogsColumns = $schema->getColumnListing('blogs');
            
            $blogsUpdateData = [];
            $blogsWhere = function($query) use ($blogsColumns) {
                $hasCondition = false;
                if (in_array('title', $blogsColumns)) {
                    $query->where('title', 'LIKE', '%The Shop%')
                          ->orWhere('title', 'LIKE', '%the shop%')
                          ->orWhere('title', 'LIKE', '%THE SHOP%');
                    $hasCondition = true;
                }
                if (in_array('content', $blogsColumns)) {
                    if ($hasCondition) {
                        $query->orWhere('content', 'LIKE', '%The Shop%')
                              ->orWhere('content', 'LIKE', '%the shop%')
                              ->orWhere('content', 'LIKE', '%THE SHOP%');
                    } else {
                        $query->where('content', 'LIKE', '%The Shop%')
                              ->orWhere('content', 'LIKE', '%the shop%')
                              ->orWhere('content', 'LIKE', '%THE SHOP%');
                    }
                }
            };
            
            if (in_array('title', $blogsColumns)) {
                $blogsUpdateData['title'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(title, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
            }
            if (in_array('content', $blogsColumns)) {
                $blogsUpdateData['content'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(content, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
            }
            
            if (!empty($blogsUpdateData)) {
                $blogsUpdated = DB::table('blogs')
                    ->where($blogsWhere)
                    ->update($blogsUpdateData);
                $updated += $blogsUpdated;
                $this->info("Updated {$blogsUpdated} blogs records");
            } else {
                $this->info("Skipped blogs table - no relevant columns found");
            }
        }
        
        // Update blog_translations table if exists
        if (DB::getSchemaBuilder()->hasTable('blog_translations')) {
            $this->info('Updating blog_translations table...');
            $schema = DB::getSchemaBuilder();
            $blogTransColumns = $schema->getColumnListing('blog_translations');
            
            $blogTransUpdateData = [];
            $blogTransWhere = function($query) use ($blogTransColumns) {
                $hasCondition = false;
                if (in_array('title', $blogTransColumns)) {
                    $query->where('title', 'LIKE', '%The Shop%')
                          ->orWhere('title', 'LIKE', '%the shop%')
                          ->orWhere('title', 'LIKE', '%THE SHOP%');
                    $hasCondition = true;
                }
                if (in_array('content', $blogTransColumns)) {
                    if ($hasCondition) {
                        $query->orWhere('content', 'LIKE', '%The Shop%')
                              ->orWhere('content', 'LIKE', '%the shop%')
                              ->orWhere('content', 'LIKE', '%THE SHOP%');
                    } else {
                        $query->where('content', 'LIKE', '%The Shop%')
                              ->orWhere('content', 'LIKE', '%the shop%')
                              ->orWhere('content', 'LIKE', '%THE SHOP%');
                    }
                }
            };
            
            if (in_array('title', $blogTransColumns)) {
                $blogTransUpdateData['title'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(title, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
            }
            if (in_array('content', $blogTransColumns)) {
                $blogTransUpdateData['content'] = DB::raw("REPLACE(REPLACE(REPLACE(COALESCE(content, ''), 'The Shop', 'ONFA Shop'), 'the shop', 'ONFA Shop'), 'THE SHOP', 'ONFA SHOP')");
            }
            
            if (!empty($blogTransUpdateData)) {
                $blogTranslationsUpdated = DB::table('blog_translations')
                    ->where($blogTransWhere)
                    ->update($blogTransUpdateData);
                $updated += $blogTranslationsUpdated;
                $this->info("Updated {$blogTranslationsUpdated} blog_translations records");
            } else {
                $this->info("Skipped blog_translations table - no relevant columns found");
            }
        }
        
        // Clear cache
        $this->info('Clearing cache...');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        
        $this->info("✅ Update completed! Total records updated: {$updated}");
        $this->info('All instances of "The Shop" have been replaced with "ONFA Shop"');
        
        return 0;
    }
}
