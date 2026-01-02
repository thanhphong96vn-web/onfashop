<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategorySingleCollection extends JsonResource
{   
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $banner = api_asset($this->banner);
        $icon = api_asset($this->icon);
        return [
            'name' => $this->getTranslation('name'),
            'banner' => $banner && $banner !== '' ? $banner : null,
            'icon' => $icon && $icon !== '' ? $icon : null,
            'slug' => $this->slug,
        ];
    }
}
