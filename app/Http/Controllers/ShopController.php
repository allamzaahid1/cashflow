<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShopRequest;
use App\Services\ShopService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        protected ShopService $shopService,
    ) {
    }

    public function create(): View
    {
        return view('shop.create');
    }

    public function store(StoreShopRequest $request): RedirectResponse
    {
        $this->shopService->create(
            auth()->user(),
            $request->validated(),
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Toko berhasil dibuat.');
    }
}