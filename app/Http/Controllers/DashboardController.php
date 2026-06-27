<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {
    }

    /**
     * Display the dashboard.
     */
    public function __invoke(): View|RedirectResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create');
        }

        $metrics = $this->dashboardService->getMetrics($shop);
        $recentTransactions = $this->dashboardService->getRecentTransactions($shop);
        $weeklyData = $this->dashboardService->getWeeklyChartData($shop);

        return view('dashboard.index', compact('metrics', 'recentTransactions', 'weeklyData', 'shop'));
    }
}