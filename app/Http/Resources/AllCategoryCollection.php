<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Utility\CategoryUtility;

class AllCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $childrens = Category::whereIn('id', CategoryUtility::children_ids($data->id))->get();
                $banner = api_asset($data->banner);
                $icon = api_asset($data->icon);
                return [
                    'name' => $data->getTranslation('name'),
                    'banner' => $banner && $banner !== '' ? $banner : null,
                    'icon' => $icon && $icon !== '' ? $icon : null,
                    'slug' => $data->slug,
                    'children' => new CategoryCollection($childrens),
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
