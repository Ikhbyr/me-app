<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent :: toArray($request);

        return [
            'id' => $this->id,
            'userid' => $this->cust_userid,
            'is_read' => $this->is_read,
            'title' => $this->notification->title,
            'description' => $this->notification->description,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_diff' => $this->created_at->diffForHumans(),
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
        ];
    }
}
