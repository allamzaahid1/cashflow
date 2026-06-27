<aside
    class="flex h-screen w-64 flex-col bg-slate-900 text-white">

    {{-- Logo --}}
    <div class="border-b border-slate-800 p-6">

        <h1 class="text-xl font-bold">
            CashFlow
        </h1>

        <p class="mt-1 text-sm text-slate-400">

            Management System

        </p>

    </div>

    {{-- Menu --}}
    <nav class="flex-1 space-y-2 p-4">

        <x-layout.menu-item
    route="dashboard"
    icon="layout-dashboard"
    title="Dashboard"/>

    <x-layout.menu-item
        route="transactions.index"
        icon="wallet"
        title="Catat Transaksi"/>

    <x-layout.menu-item
        route="reports.index"
        icon="chart-column"
        title="Rekap Penjualan"/>

    <x-layout.menu-item
        route="withdrawals.index"
        icon="arrow-up-right"
        title="Penarikan Dana"/>

    <x-layout.menu-item
        route="settings.index"
        icon="settings"
        title="Pengaturan Toko"/>

    </nav>

    {{-- User --}}
    <div class="border-t border-slate-800 p-4">

        <p class="font-medium text-white">

            {{ auth()->user()->name }}

        </p>

        <p class="text-sm text-slate-400">

            Administrator

        </p>

    </div>

</aside>