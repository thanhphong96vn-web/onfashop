<?php

namespace MehediIitdu\CoreComponentRepository;
use App\Models\Addon;
use Cache;

class CoreComponentRepository
{
    public static function instantiateShopRepository() {
        // License check disabled
        return;
    }

    protected static function serializeObjectResponse($zn, $request_data_json) {
        $header = array(
            'Content-Type:application/json'
        );
        $stream = curl_init();

        curl_setopt($stream, CURLOPT_URL, $zn);
        curl_setopt($stream, CURLOPT_HTTPHEADER, $header);
        curl_setopt($stream, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($stream, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($stream, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($stream, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($stream, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $rn = curl_exec($stream);
        curl_close($stream);
        return $rn;
    }

    protected static function finalizeRepository($rn) {
        // License check disabled
        return;
    }

    public static function initializeCache() {
        // License check disabled - auto activate all addons
        foreach(Addon::all() as $addon){
            if ($addon->activated == 0) {
                $addon->activated = 1;
                $addon->save();
            }
            Cache::rememberForever($addon->unique_identifier.'-purchased', function () {
                return 'yes';
            });
        }
    }

    public static function finalizeCache($addon){
        // License check disabled - auto activate addon
        $addon->activated = 1;
        $addon->save();
        return;
    } 
}
