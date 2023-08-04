<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CountryStatus;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Country;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:update,user')->only('edit', 'update');
        $this->middleware('can:delete,user')->only('destroy');
    }

    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $users = User::with('roles')
            ->filter($request->all())
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $roles = Role::getAvailableRoles();
        $countries = Country::query()->where('status', '=', CountryStatus::Active)->pluck('name', 'id')->all();
        $shops = Shop::query()->where('status', '=', ShopStatus::Active)->pluck('name', 'id')->all();

        return view('admin.users.create', compact('roles', 'countries', 'shops'));
    }

    /**
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($request->get('password'));

        $user = new User($data);
        $user->save();

        $user->roles()->sync($request->get('role_id'));

        if ($request->get('api_token') === 'on') {
            $accessToken = $user->createToken('api-token')->plainTextToken;

            $user->access_token = $accessToken;
            $user->save();
        }

        return redirect()->route('admin.users.index')->with('success', ['text' => 'Успешно добавлено!']);
    }

    /**
     * @param User $user
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(User $user): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * @param User $user
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(User $user): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $roles = Role::getAvailableRoles();
        $countries = Country::query()->where('status', '=', CountryStatus::Active)->pluck('name', 'id')->all();
        $shops = Shop::query()->where('status', '=', ShopStatus::Active)->pluck('name', 'id')->all();

        return view('admin.users.edit', compact('user', 'roles', 'countries', 'shops'));
    }

    /**
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['attached_shops'] = $request->get('attached_shops') ?? [];
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->get('password'));
        }

        $user->update($data);

        $user->roles()->sync($request->get('role_id'));

        return redirect()->route('admin.users.index')->with('success', ['text' => 'Успешно обновлено!']);
    }

    /**
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->back()->with('success', ['text' => 'Успешно удалено!']);
    }
}
