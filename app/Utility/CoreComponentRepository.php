<?php

namespace App\Utility;

use App\Models\Addon;
use Cache;

/**
 * Override CoreComponentRepository to remove all license checks
 * This class replaces the vendor version permanently
 */
class CoreComponentRepository
{
    /**
     * Instantiate shop repository - license check disabled
     * 
     * @return void
     */
    public static function instantiateShopRepository() {
        // License check completely removed
        return;
    }

    /**
     * Initialize cache for addons - auto activate all addons
     * 
     * @return void
     */
    public static function initializeCache() {
        // Auto activate all addons without license check
        try {
            foreach(Addon::all() as $addon){
                if ($addon->activated == 0) {
                    $addon->activated = 1;
                    $addon->save();
                }
                Cache::rememberForever($addon->unique_identifier.'-purchased', function () {
                    return 'yes';
                });
            }
        } catch (\Exception $e) {
            // Silently fail if Addon model doesn't exist or other errors
        }
    }

    /**
     * Finalize cache - auto activate addon instead of deactivating
     * 
     * @param mixed $addon
     * @return void
     */
    public static function finalizeCache($addon){
        // Auto activate addon instead of deactivating
        try {
            if ($addon && is_object($addon)) {
                $addon->activated = 1;
                $addon->save();
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return;
    }
}
