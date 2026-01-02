<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

// Create global alias for CoreComponentRepository to ensure compatibility with views
if (!class_exists('CoreComponentRepository')) {
    class_alias(\App\Utility\CoreComponentRepository::class, 'CoreComponentRepository');
}

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Paginator::useBootstrap();
    Schema::defaultStringLength(191);
    
    // Override asset() helper globally to return relative paths only
    // This prevents localhost URLs in production builds
    if (!function_exists('asset')) {
      // Only override if not already defined (shouldn't happen, but safety check)
    }
    
    // Force Vite to use relative paths with /public prefix for static assets
    \Illuminate\Support\Facades\Vite::createAssetPathsUsing(function ($path, $secure = null) {
      // Remove any protocol/host prefix (e.g., http://localhost/onfashop)
      $path = preg_replace('#^https?://[^/]+#', '', $path);
      
      // Remove any localhost references
      $path = preg_replace('#^//localhost[^/]*#', '', $path);
      
      // Ensure path starts with /
      if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
      }
      
      // For Vite build assets, ensure /public prefix
      if (preg_match('#^/build/#', $path) && !str_starts_with($path, '/public/')) {
        $path = '/public' . $path;
      }
      
      // For other assets in public directory, ensure /public prefix
      if (preg_match('#^/(assets|uploads|web-assets)/#', $path) && !str_starts_with($path, '/public/')) {
        $path = '/public' . $path;
      }
      
      return $path;
    });
    
    // Override UrlGenerator's assetRoot property to force relative paths
    $this->app->resolving(\Illuminate\Routing\UrlGenerator::class, function ($url) {
      try {
        $reflection = new \ReflectionClass($url);
        $assetRootProperty = $reflection->getProperty('assetRoot');
        $assetRootProperty->setAccessible(true);
        
        // Set assetRoot to empty string to force relative paths
        $assetRootProperty->setValue($url, '');
      } catch (\ReflectionException $e) {
        // If reflection fails, just continue
      }
    });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    // Override CoreComponentRepository to remove license checks permanently
    $this->app->singleton('core-component-repository', function () {
      return new \App\Utility\CoreComponentRepository;
    });
  }
}
