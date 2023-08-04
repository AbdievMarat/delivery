<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CountryStatus;
use App\Enums\ShopStatus;
use App\Enums\YandexTariff;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCountryRequest;
use App\Http\Requests\Admin\UpdateCountryRequest;
use App\Models\Country;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:delete,country')->only('destroy');
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $countries = Country::all();

        return view('admin.countries.index', compact('countries'));
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $statuses = CountryStatus::values();
        $yandexTariffs = YandexTariff::values();

        return view('admin.countries.create', compact('statuses', 'yandexTariffs'));
    }

    /**
     * @param StoreCountryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $company = new Country($request->validated());
        $company->save();

        return redirect()->route('admin.countries.index')->with('success', ['text' => 'Успешно добавлено!']);
    }

    /**
     * @param Country $country
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(Country $country): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.countries.show', compact('country'));
    }

    /**
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
     * @param UpdateCountryRequest $request
     * @param Country $country
     * @return RedirectResponse
     */
    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $data = $request->validated();
        $data['yandex_tariffs'] = $request->get('yandex_tariffs') ?? [];

        $country->update($data);

        return redirect()->route('admin.countries.index')->with('success', ['text' => 'Успешно обновлено!']);
    }

    /**
     * @param Country $country
     * @return RedirectResponse
     */
    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->back()->with('success', ['text' => 'Успешно удалено!']);
    }

    /**
     * @param Country $country
     * @return JsonResponse
     */
    public function getShopsOfCountry(Country $country): JsonResponse
    {
        $shops = $country
            ->shops()
            ->where('status', '=', ShopStatus::Active)
            ->get()
            ->toArray();

        return response()->json(['shops' => $shops]);
    }
}
