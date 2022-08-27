<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HistoryResources extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if (!isset($this->collection['histories']))
            $histories = (object)[];

        $histories = $this->collection['histories'];

        return [
            'data' =>  $histories,
            'message' => $this->collection['message'],
            'success' => $this->collection['success']
        ];
    }
}
