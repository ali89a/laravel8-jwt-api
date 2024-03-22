<?php

namespace App\Http\Resources\V1;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            "created_at" => Carbon::parse($this->created_at)->format('d F Y'),
            'updated_at' => date('d M Y g:i A', strtotime($this->updated_at)),
            'day' => Carbon::parse($this->created_at)->isoFormat('Do MMMM'),
        ];
    }
}
