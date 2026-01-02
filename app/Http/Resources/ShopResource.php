<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Calculate products count with filter (published and approved)
        $products_count = \App\Models\Product::where('shop_id', $this->id)
            ->where('published', 1)
            ->where('approved', 1)
            ->count();
        
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'logo' => api_asset($this->logo),
            'banners' => $this->convertBanners(),
            'products_banners' => get_banners($this->products_banners),
            'categories' => new CategoryCollection($this->categories),
            'rating' => (double) $this->rating,
            'reviews_count' => $this->reviews_count,
            'products_count' => $products_count,
            'since' => $this->created_at ? $this->created_at->format('d M, Y') : '',
            'isVarified' => $this->verification_status == 1 ? true : false,
        ];
    }


    protected function convertBanners()
    {
        $result = array();
        foreach (explode(',', $this->banners) as $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
