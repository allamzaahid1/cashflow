@extends('layouts.app')

@section('title', 'Penarikan Dana')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');
</style>

<div class="space-y-6" x-data="{ 
    nominal: '', 
    adminFee: 2500, 
    available: {{ (float) $availableBalance }},
    hasMethods: {{ $paymentMethods->isNotEmpty() ? 'true' : 'false' }}
}">
    <!-- Header -->
    <div>
        <h1 class="text-xl font-bold text-text-primary" style="font-family: 'Plus Jakarta Sans', sans-serif;">
            Penarikan Dana
        </h1>
        <p class="text-sm text-text-secondary mt-0.5">Tarik saldo toko ke rekening bank pilihan</p>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="rounded-lg bg-success-bg p-4 text-sm text-success-text border border-success-text/20">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-danger-bg p-4 text-sm text-danger-text border border-danger-text/20">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Balance card -->
    <div class="rounded-2xl p-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #16a34a 0%, #059669 100%)">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-green-100 text-xs font-semibold uppercase tracking-wider">Saldo Tersedia</p>
                <p class="text-3xl font-bold mt-1.5" style="font-family: 'JetBrains Mono', monospace;">
                    Rp {{ number_format($availableBalance, 0, ',', '.') }}
                </p>
                <p class="text-green-200 text-xs mt-1">Diperbarui secara real-time</p>
            </div>
            <div class="text-right">
                <p class="text-green-200 text-xs font-semibold uppercase tracking-wider">Kuota Penarikan</p>
                <p class="text-3xl font-bold mt-1.5" style="font-family: 'JetBrains Mono', monospace;">
                    {{ $monthlyQuotaUsed }}<span class="text-green-300 text-xl">/5</span>
                </p>
                <p class="text-green-200 text-xs mt-1">Periode {{ now()->locale('id')->isoFormat('MMMM YYYY') }}</p>
            </div>
        </div>
        <!-- Progress bar -->
        <div class="mt-5 bg-green-700/40 rounded-full h-1.5">
            <div class="bg-white/80 rounded-full h-1.5 transition-all" style="width: {{ ($monthlyQuotaUsed / 5) * 100 }}%"></div>
        </div>
        <p class="text-green-200 text-xs mt-1.5">{{ $monthlyQuotaUsed }} dari 5 penarikan terpakai bulan ini</p>
    </div>

    <!-- Main split -->
    <div class="grid grid-cols-5 gap-5">
        <!-- Request form — 3/5 cols -->
        <div class="col-span-3 bg-bg-surface rounded-xl border border-border-base shadow-sm p-6">
            <h2 class="font-semibold text-text-primary mb-4">Form Penarikan</h2>

            @if ($paymentMethods->isEmpty())
                <div class="p-4 rounded-xl border border-warning-text/20 bg-warning-bg text-sm text-warning-text">
                    ⚠️ <strong>Metode Penarikan Belum Siap:</strong> Anda belum memiliki rekening bank atau e-wallet non-tunai yang aktif. 
                    Silakan daftarkan metode pembayaran non-tunai Anda di <a href="{{ route('settings.index') }}" class="underline font-bold text-warning-text hover:opacity-80">Pengaturan Toko</a> terlebih dahulu.
                </div>
            @else
                <form action="{{ route('withdrawals.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Nominal input -->
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">
                            Nominal Penarikan (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary text-sm font-medium">Rp</span>
                            <input
                                type="number"
                                name="amount"
                                required
                                x-model.number="nominal"
                                placeholder="0"
                                class="w-full pl-10 pr-4 py-3 text-xl font-bold text-text-primary bg-bg-base/30 border border-border-base rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                                style="font-family: 'JetBrains Mono', monospace;"
                            />
                        </div>
                        <!-- Quick selections -->
                        <div class="flex gap-2 mt-2">
                            <button
                                type="button"
                                @click="nominal = 500000"
                                class="flex-1 py-1.5 text-xs font-semibold text-success-text bg-success-bg hover:bg-success-bg/80 rounded-lg transition-colors"
                            >
                                Rp 500.000
                            </button>
                            <button
                                type="button"
                                @click="nominal = 1000000"
                                class="flex-1 py-1.5 text-xs font-semibold text-success-text bg-success-bg hover:bg-success-bg/80 rounded-lg transition-colors"
                            >
                                Rp 1.000.000
                            </button>
                            <button
                                type="button"
                                @click="nominal = 2000000"
                                class="flex-1 py-1.5 text-xs font-semibold text-success-text bg-success-bg hover:bg-success-bg/80 rounded-lg transition-colors"
                            >
                                Rp 2.000.000
                            </button>
                        </div>
                        <!-- Error feedback -->
                        <p x-show="nominal && (Number(nominal) + adminFee) > available" class="text-xs text-danger-text mt-2" x-cloak>
                            Saldo Anda tidak mencukupi untuk nominal penarikan ini beserta biaya admin.
                        </p>
                        <p x-show="nominal && nominal < 10000" class="text-xs text-danger-text mt-2" x-cloak>
                            Nominal penarikan minimal Rp 10.000.
                        </p>
                    </div>

                    <!-- Target Account -->
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Bank/Rekening Tujuan</label>
                        <div class="relative">
                            <select
                                name="payment_method_id"
                                required
                                class="w-full appearance-none px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                            >
                                <option value="" class="bg-bg-surface text-text-primary">Pilih rekening tujuan…</option>
                                @foreach ($paymentMethods as $pay)
                                    <option value="{{ $pay->id }}" class="bg-bg-surface text-text-primary">
                                        {{ $pay->name }} ({{ $pay->account_number }} - A/N {{ $pay->account_name }})
                                    </option>
                                @endforeach
                            </select>
                            <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary pointer-events-none" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                        <input
                            type="text"
                            name="notes"
                            placeholder="cth: Keperluan operasional cabang"
                            class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-all"
                        />
                    </div>

                    <!-- Hidden Submit to bind with the right panel -->
                    <button type="submit" id="submit-withdrawal-form" class="hidden"></button>
                </form>
            @endif
        </div>

        <!-- Breakdown panel — 2/5 cols -->
        <div class="col-span-2 space-y-4">
            <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-5">
                <h3 class="font-semibold text-text-primary mb-4">Rincian Biaya</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-text-secondary">Nominal Tarik</span>
                        <span class="font-semibold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                            Rp <span x-text="nominal ? Number(nominal).toLocaleString('id-ID') : '0'"></span>
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-text-secondary">Biaya Admin</span>
                        <span class="font-semibold text-text-primary" style="font-family: 'JetBrains Mono', monospace;">
                            Rp 2.500
                        </span>
                    </div>
                    <div class="border-t border-dashed border-border-base pt-3 flex justify-between items-baseline">
                        <span class="font-semibold text-text-primary text-sm">Total Potongan Saldo</span>
                        <span class="font-bold text-danger-text text-base" style="font-family: 'JetBrains Mono', monospace;">
                            Rp <span x-text="nominal ? (Number(nominal) + adminFee).toLocaleString('id-ID') : '0'"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <button
                type="button"
                @click="document.getElementById('submit-withdrawal-form').click()"
                :disabled="!nominal || nominal < 10000 || (Number(nominal) + adminFee) > available || !hasMethods"
                class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold py-4 rounded-xl transition-all shadow-md text-sm flex items-center justify-center gap-2"
                style="font-family: 'Plus Jakarta Sans', sans-serif;"
            >
                Proses Penarikan
            </button>

            <div class="bg-warning-bg border border-warning-text/10 rounded-xl p-4">
                <p class="text-xs text-warning-text font-medium">
                    ⚠️ Proses transfer membutuhkan waktu 1×24 jam kerja setelah disetujui.
                </p>
            </div>
        </div>
    </div>

    <!-- History list -->
    <div class="bg-bg-surface rounded-xl border border-border-base shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-border-base">
            <h2 class="font-semibold text-text-primary">Riwayat Penarikan</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-bg-base/50">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Tujuan Rekening</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Nominal</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Biaya Admin</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-text-secondary uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-base">
                @forelse ($withdrawals as $w)
                    <tr class="hover:bg-bg-base/30 transition-colors">
                        <td class="px-5 py-3.5 text-xs text-text-secondary" style="font-family: 'JetBrains Mono', monospace;">
                            {{ $w->withdrawal_date->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-text-primary font-semibold text-sm">
                            {{ $w->paymentMethod->name }} ({{ $w->paymentMethod->account_number }})
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-text-primary text-sm" style="font-family: 'JetBrains Mono', monospace;">
                            Rp {{ number_format($w->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-text-secondary" style="font-family: 'JetBrains Mono', monospace;">
                            Rp {{ number_format($w->admin_fee, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{
                                $w->status === 'approved'
                                    ? 'bg-success-bg text-success-text'
                                    : ($w->status === 'rejected'
                                        ? 'bg-danger-bg text-danger-text'
                                        : 'bg-warning-bg text-warning-text')
                            }}">
                                {{ $w->status === 'approved' ? 'Sukses' : ($w->status === 'rejected' ? 'Gagal' : 'Pending') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-text-secondary text-sm">
                            Belum ada riwayat penarikan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination Links -->
        @if ($withdrawals->hasPages())
            <div class="px-5 py-4 border-t border-border-base">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection