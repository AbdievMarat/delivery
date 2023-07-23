<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator')) {
            return redirect()->route('admin.orders.index');
        } else if (Auth::user()->hasRole('accountant')) {
            return redirect()->route('order_report');
        } else {
            return redirect()->route('shop.orders.index');
        }
    }
}
