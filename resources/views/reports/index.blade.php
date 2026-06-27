@extends('layouts.app')

@section('title', 'Rekap Penjualan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-text-primary" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                Rekap Penjualan
            </h1>
            <p class="text-sm text-text-secondary mt-0.5">
                Laporan Arus Kas ({{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }})
            </p>
        </div>
        
        <!-- Export Actions -->
        <div class="flex items-center gap-2">
            <a
                href="{{ route('reports.export.pdf', request()->query()) }}"
                class="flex items-center gap-2 border border-red-200 dark:border-red-950 text-red-700 dark:text-red-400 bg-red-50/50 dark:bg-red-950/20 hover:bg-red-55 px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
            >
                <x-lucide-file-text class="w-4 h-4" />
                Unduh PDF
            </a>
            <a
                href="{{ route('reports.export.excel', request()->query()) }}"
                class="flex items-center gap-2 border border-green-200 dark:border-green-950 text-green-700 dark:text-green-400 bg-green-50/50 dark:bg-green-950/20 hover:bg-green-55 px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
            >
                <x-lucide-file-spreadsheet class="w-4 h-4" />
                Unduh Excel
            </a>
        </div>
    </div>

    <!-- Summary aggregates -->
    <div class="grid grid-cols-3 gap-5">
        <!-- Total Kas Masuk -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-success-text text-lg">↓</span>
                <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Total Kas Masuk</p>
            </div>
            <p class="text-2xl font-extrabold text-success-text" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
            </p>
        </div>

        <!-- Total Kas Keluar -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-danger-text text-lg">↑</span>
                <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Total Kas Keluar</p>
            </div>
            <p class="text-2xl font-extrabold text-danger-text" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}
            </p>
        </div>

        <!-- Saldo Akhir -->
        <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-blue-600 dark:text-blue-400 text-lg">⚖</span>
                <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider">Saldo Akhir</p>
            </div>
            <p class="text-2xl font-extrabold @if($summary['net_cash_flow'] >= 0) text-success-text @else text-danger-text @endif" style="font-family: 'JetBrains Mono', monospace;">
                Rp {{ number_format($summary['net_cash_flow'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Filter form panel -->
    <div class="bg-bg-surface rounded-xl border border-border-base p-5 shadow-sm">
        <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-5 gap-4 items-end">
            <!-- Start Date -->
            <div>
                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $filters['start_date'] }}"
                    class="w-full px-3 py-2 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all dark:color-scheme-dark"
                />
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $filters['end_date'] }}"
                    class="w-full px-3 py-2 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all dark:color-scheme-dark"
                />
            </div>

            <!-- Type -->
            <div>
                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tipe Transaksi</label>
                <select
                    name="type"
                    class="w-full px-3 py-2 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                >
                    <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }} class="bg-bg-surface text-text-primary">Semua Tipe</option>
                    <option value="income" {{ $filters['type'] === 'income' ? 'selected' : '' }} class="bg-bg-surface text-text-primary">Pemasukan</option>
                    <option value="expense" {{ $filters['type'] === 'expense' ? 'selected' : '' }} class="bg-bg-surface text-text-primary">Pengeluaran</option>
                </select>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Kategori</label>
                <select
                    name="category_id"
                    class="w-full px-3 py-2 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                >
                    <option value="" class="bg-bg-surface text-text-primary">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $filters['category_id'] == $cat->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                            {{ $cat->name }} ({{ $cat->type === 'income' ? 'Masuk' : 'Keluar' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Metode Pembayaran</label>
                <select
                    name="payment_method_id"
                    class="w-full px-3 py-2 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                >
                    <option value="" class="bg-bg-surface text-text-primary">Semua Metode</option>
                    @foreach ($paymentMethods as $pay)
                        <option value="{{ $pay->id }}" {{ $filters['payment_method_id'] == $pay->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                            {{ $pay->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-span-5 flex justify-end gap-2 pt-2 border-t border-border-base mt-2">
                <a
                    href="{{ route('reports.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-text-secondary bg-bg-base hover:bg-bg-base/80 rounded-lg transition-colors"
                >
                    Reset Filter
                </a>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm"
                >
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table & Pagination -->
    <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm overflow-hidden flex flex-col justify-between">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-bg-base/50">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Tanggal & Waktu</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Kode</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Kategori</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Metode</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Keterangan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Bukti</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                @forelse ($transactions as $tx)
                    <tr class="hover:bg-bg-base/30 transition-colors">
                        <td class="px-5 py-3.5 text-text-secondary">
                            {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}, 
                            <span class="text-xs font-mono text-text-secondary/60">{{ $tx->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                            {{ $tx->transaction_code }}
                        </td>
                        <td class="px-5 py-3.5 text-text-primary">
                            {{ $tx->category->name }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{
                                $tx->paymentMethod->type === 'cash' ? 'bg-warning-bg text-warning-text' : 'bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400'
                            }}">
                                {{ $tx->paymentMethod->name }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-text-secondary text-sm">
                            {{ $tx->description ?: '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($tx->proof_image)
                                <a
                                    href="{{ asset('storage/' . $tx->proof_image) }}"
                                    target="_blank"
                                    class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1"
                                >
                                    <x-lucide-image class="w-3.5 h-3.5" /> Lihat Bukti
                                </a>
                            @else
                                <span class="text-xs text-text-secondary/40">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold {{
                            $tx->category->type === 'income' ? 'text-success-text' : 'text-danger-text'
                        }}" style="font-family: 'JetBrains Mono', monospace;">
                            {{ $tx->category->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-text-secondary text-sm">
                            Tidak ditemukan transaksi yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination Links -->
        @if ($transactions->hasPages())
            <div class="px-5 py-4 border-t border-border-base flex items-center justify-between">
                <p class="text-xs text-text-secondary">
                    Menampilkan {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi
                </p>
                <div class="flex items-center gap-2">
                    {{ $transactions->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection