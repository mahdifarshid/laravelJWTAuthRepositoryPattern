<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LoginResources extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if (!isset($this->collection['accessToken']))
            $token = (object)[];

        $token = [
            'token' => $this->collection['accessToken'],
            'token_type' => 'Bearer'
        ];

        return [
            'data' =>  $token,
            'message' => $this->collection['message'],
            'success' => $this->collection['success']
        ];
    }
}
