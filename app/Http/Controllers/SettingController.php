<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $shop = auth()->user()->shop;

        $categories = $shop ? $shop->categories()->orderBy('name')->get() : collect();
        $paymentMethods = $shop ? $shop->paymentMethods()->orderBy('name')->get() : collect();

        return view('settings.index', compact('categories', 'paymentMethods', 'shop'));
    }
}
