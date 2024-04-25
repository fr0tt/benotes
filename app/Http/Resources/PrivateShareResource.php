<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// use Illuminate\Http\Resources\Json\ResourceCollection;

class PrivateShareResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'root_collection_id' => $this->collection_id,
            'created_by'         => $this->created_by,
            'user'               => User::find($this->user_id),
        ];
    }
}
