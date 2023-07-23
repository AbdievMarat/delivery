<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CountryStatus;
use App\Enums\YandexTariff;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCountryRequest;
use App\Http\Requests\Admin\UpdateCountryRequest;
use App\Models\Country;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:delete,country')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::all();
        return view('admin.countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $statuses = CountryStatus::values();
        $yandexTariffs = YandexTariff::values();

        return view('admin.countries.create', compact('statuses', 'yandexTariffs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $company = new Country($request->validated());
        $company->save();

        return redirect()->route('admin.countries.index')->with('success', ['text' => 'Успешно добавлено!']);
    }

    /**
     * Display the specified resource.
     * @param Country $country
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(Country $country): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.countries.show', compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param Country $country
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Country $country): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $statuses = CountryStatus::values();
        $yandexTariffs = YandexTariff::values();

        return view('admin.countries.edit', compact('country', 'statuses', 'yandexTariffs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $data = $request->validated();
        if (!$request->has('yandex_tariffs')) {
            $data['yandex_tariffs'] = [];
        }
        $country->update($data);

        return redirect()->route('admin.countries.index')->with('success', ['text' => 'Успешно обновлено!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->back()->with('success', ['text' => 'Успешно удалено!']);
    }
}
