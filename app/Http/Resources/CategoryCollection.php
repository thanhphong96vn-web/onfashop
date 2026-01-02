<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                // Kiểm tra xem banner và icon có tồn tại không trước khi gọi api_asset
                $banner = null;
                $icon = null;
                
                if ($data->banner && $data->banner != '' && $data->banner != 'null') {
                    $bannerUrl = api_asset($data->banner);
                    $banner = ($bannerUrl && $bannerUrl !== '' && $bannerUrl !== 'null') ? $bannerUrl : null;
                }
                
                if ($data->icon && $data->icon != '' && $data->icon != 'null') {
                    $iconUrl = api_asset($data->icon);
                    $icon = ($iconUrl && $iconUrl !== '' && $iconUrl !== 'null') ? $iconUrl : null;
                }
                
                return [
                    'id' => $data->id,
                    'name' => $data->getTranslation('name'),
                    'banner' => $banner,
                    'icon' => $icon,
                    'slug' => $data->slug,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
