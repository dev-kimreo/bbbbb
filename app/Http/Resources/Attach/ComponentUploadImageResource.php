<?php

namespace App\Http\Resources\Attach;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentUploadImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return Arrayable
     *
     */
    public function toArray($request): Arrayable
    {
        $data = clone $this->resource;
        return collect($data->attachFile)->put('componentUploadImage', $this->resource);
    }
}
