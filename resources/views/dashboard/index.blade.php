@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');
</style>

<div class="space-y-5">
    <!-- Page header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-text-primary" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                Dashboard
            </h1>
            <p class="text-sm text-text-secondary mt-0.5">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-success-bg text-success-text">
                ● Toko Buka
            </span>
        </div>
    </div>

    <!-- Metric cards -->
    <div class="grid grid-cols-4 gap-4">
        <!-- Total Pemasukan Hari Ini -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-text-secondary leading-snug pr-2">Total Pemasukan Hari Ini</p>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-success-bg">
                    <x-lucide-trending-up class="w-4 h-4 text-success-text" />
                </div>
            </div>
            <p class="text-xl font-bold text-success-text" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($metrics['today_income'], 0, ',', '.') }}
            </p>
            <p class="text-xs mt-1 @if($metrics['percentage_diff'] >= 0) text-success-text @else text-danger-text @endif">
                {{ $metrics['percentage_diff'] >= 0 ? '+' : '' }}{{ $metrics['percentage_diff'] }}% vs kemarin
            </p>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-text-secondary leading-snug pr-2">Total Transaksi</p>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50 dark:bg-blue-950/20">
                    <x-lucide-shopping-cart class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <p class="text-xl font-bold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                {{ $metrics['today_transaction_count'] }}
            </p>
            <p class="text-xs text-text-secondary mt-1">Hari ini</p>
        </div>

        <!-- Pembayaran Cash -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-text-secondary leading-snug pr-2">Pembayaran Cash</p>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-warning-bg">
                    <x-lucide-banknote class="w-4 h-4 text-warning-text" />
                </div>
            </div>
            <p class="text-xl font-bold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($metrics['cash_income'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-text-secondary mt-1">{{ $metrics['cash_percentage'] }}% dari total</p>
        </div>

        <!-- Pembayaran QRIS -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-medium text-text-secondary leading-snug pr-2">Pembayaran QRIS</p>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-purple-50 dark:bg-purple-950/20">
                    <x-lucide-qr-code class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
            <p class="text-xl font-bold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($metrics['qris_income'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-text-secondary mt-1">{{ $metrics['qris_percentage'] }}% dari total</p>
        </div>
    </div>

    <!-- Main split -->
    <div class="grid grid-cols-3 gap-5">
        <!-- Recent transactions — 2/3 -->
        <div class="col-span-2 bg-bg-surface rounded-xl border border-border-base shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between px-5 py-4 border-b border-border-base">
                    <h2 class="font-semibold text-text-primary">Transaksi Terbaru</h2>
                    <a
                        href="{{ route('reports.index') }}"
                        class="text-xs text-success-text font-semibold hover:underline flex items-center gap-1"
                    >
                        Lihat Semua <x-lucide-chevron-right class="w-3 h-3" />
                    </a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-bg-base/50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wider">Waktu</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wider">Keterangan</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wider">Metode</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-text-secondary uppercase tracking-wider">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-base">
                        @forelse ($recentTransactions as $tx)
                            <tr class="hover:bg-bg-base/30 transition-colors">
                                <td class="px-5 py-3.5 text-xs text-text-secondary" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ $tx->created_at->format('H:i') }}
                                </td>
                                <td class="px-5 py-3.5 text-text-primary text-sm">
                                    {{ $tx->description ?: $tx->category->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{
                                            $tx->paymentMethod->type === 'cash'
                                                ? 'bg-warning-bg text-warning-text'
                                                : 'bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400'
                                        }}"
                                    >
                                        {{ $tx->paymentMethod->name }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-sm {{
                                    $tx->category->type === 'income' ? 'text-success-text' : 'text-danger-text'
                                }}" style="font-family: 'JetBrains Mono', monospace;">
                                    {{ $tx->category->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-text-secondary text-sm">
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right action panel — 1/3 -->
        <div class="space-y-4">
            <!-- CTA Button -->
            <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-5">
                <a
                    href="{{ route('transactions.index') }}"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold py-5 px-5 rounded-xl text-base transition-all shadow-md flex items-center justify-center gap-2"
                    style="font-family: 'Plus Jakarta Sans', sans-serif;"
                >
                    <x-lucide-plus class="w-5 h-5" />
                    Catat Transaksi Baru
                </a>
                <p class="text-xs text-text-secondary text-center mt-3">Tekan Ctrl+N untuk pintasan</p>
            </div>

            <!-- Weekly Chart -->
            <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-5">
                <h3 class="text-sm font-semibold text-text-primary mb-1">Pendapatan Minggu Ini</h3>
                <p class="text-xs text-text-secondary mb-6">{{ $weeklyData['start_date'] }} – {{ $weeklyData['end_date'] }}</p>
                
                <!-- SVG/HTML Flex Bar Chart (Pure CSS/Blade rendering) -->
                <div class="flex justify-between items-end h-32 px-1 relative">
                    <!-- Baseline grid line -->
                    <div class="absolute bottom-0 left-0 right-0 border-b border-border-base"></div>

                    @foreach ($weeklyData['chart_data'] as $day)
                        <div class="flex flex-col items-center flex-1 group relative">
                            <!-- Tooltip on hover -->
                            <div class="absolute bottom-full mb-2 bg-slate-900 text-white text-xs font-semibold px-2 py-1 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                                Rp {{ number_format($day['amount'], 0, ',', '.') }}
                            </div>
                            
                            <!-- Bar track & fill -->
                            <div class="w-4 bg-bg-base rounded-full h-24 flex items-end overflow-hidden mb-2">
                                <div class="bg-emerald-600 w-full rounded-full transition-all duration-500 ease-out" 
                                     style="height: {{ $day['percentage'] }}%;"></div>
                            </div>
                            <!-- Day Label -->
                            <span class="text-[10px] font-semibold text-text-secondary tracking-wider">
                                {{ $day['day'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Access Links -->
            <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-text-primary">Akses Cepat</h3>
                </div>
                <div class="space-y-2">
                    <a
                        href="{{ route('reports.index') }}"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-text-secondary hover:bg-bg-base hover:text-emerald-600 transition-colors text-left"
                    >
                        Rekap Harian
                        <x-lucide-chevron-right class="w-4 h-4 text-text-secondary/50" />
                    </a>
                    <a
                        href="{{ route('withdrawals.index') }}"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-text-secondary hover:bg-bg-base hover:text-emerald-600 transition-colors text-left"
                    >
                        Penarikan Dana
                        <x-lucide-chevron-right class="w-4 h-4 text-text-secondary/50" />
                    </a>
                    <a
                        href="{{ route('settings.index') }}"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-text-secondary hover:bg-bg-base hover:text-emerald-600 transition-colors text-left"
                    >
                        Pengaturan QRIS
                        <x-lucide-chevron-right class="w-4 h-4 text-text-secondary/50" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection