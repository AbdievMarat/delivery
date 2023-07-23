<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = [
                'product_sku' => $item->product_sku,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
            ];
        }

        return [
            'order_number' => $this->order_number,
            'client_phone' => $this->client_phone,
            'client_name' => $this->client_name,
            'country' => $this->country->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'flat' => $this->flat,
            'comment_for_driver' => $this->comment_for_driver,
            'delivery_mode' => $this->delivery_mode,
            'delivery_date' => $this->delivery_date,
            'order_price' => $this->order_price,
            'payment_cash' => $this->payment_cash,
            'payment_bonuses' => $this->payment_bonuses,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'items' => $items,
        ];
    }
}
