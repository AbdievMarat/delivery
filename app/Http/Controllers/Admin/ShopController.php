<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShopRequest;
use App\Http\Requests\Admin\UpdateShopRequest;
use App\Models\Country;
use App\Models\Shop;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:delete,shop')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $shops = Shop::query()
            ->select("shops.*", "countries.name AS country_name")
            ->join("countries", "countries.id", "=", "shops.country_id")
            ->filter()
            ->orderBy('shops.id')
            ->paginate(10)
            ->withQueryString();

        $countries = Country::query()->pluck('name', 'id')->all();
        $statuses = ShopStatus::values();

        return view('admin.shops.index', compact('shops', 'countries', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::query()->pluck('name', 'id')->all();
        $statuses = ShopStatus::values();

        return view('admin.shops.create', compact('countries', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopRequest $request): RedirectResponse
    {
        $shop = new Shop($request->validated());
        $shop->save();

        return redirect()->route('admin.shops.index')->with('success', ['text' => 'Успешно добавлено!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.shops.show', compact('shop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::query()->pluck('name', 'id')->all();
        $statuses = ShopStatus::values();

        return view('admin.shops.edit', compact('shop', 'countries', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopRequest $request, Shop $shop): Application|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $shop->update($request->validated());
        $previous_url = $request->post('previous_url');

        return redirect($previous_url)->with('success', ['text' => 'Успешно обновлено!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop): RedirectResponse
    {
        $shop->delete();

        return redirect()->back()->with('success', ['text' => 'Успешно удалено!']);
    }
}
