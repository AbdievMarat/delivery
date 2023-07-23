<?php

namespace App\Actions\Admin\Order;

use App\Enums\DeliveryMode;
use App\Enums\OrderSource;
use App\Enums\PaymentStatus;
use App\Models\Country;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class CreateAction
{
    public function __invoke(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::query()->pluck('name', 'id')->all();
        $deliveryModes = DeliveryMode::values();
        $sources = [OrderSource::Other->value => OrderSource::Other->value];

        return view('admin.orders.create', compact('sources', 'deliveryModes', 'countries'));
    }
}
