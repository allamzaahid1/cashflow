@extends('layouts.app')

@section('title', 'Catat Transaksi')

@section('content')
<div class="space-y-6"
     x-data="{ 
         activeTab: '{{ old('category_id') && $expenseCategories->contains('id', old('category_id')) ? 'pengeluaran' : 'pemasukan' }}',
         payMethodPemasukan: '{{ old('payment_method_type', 'tunai') }}',
         payMethodPengeluaran: '{{ old('payment_method_type', 'tunai') }}',
         selectedFilePemasukan: null,
         selectedFilePengeluaran: null,
         hasNonCashMethods: {{ $nonCashPaymentMethods->isNotEmpty() ? 'true' : 'false' }}
     }">

    <!-- Page header -->
    <div>
        <h1 class="text-xl font-bold text-text-primary" style="font-family: 'Plus Jakarta Sans', sans-serif;">
            Catat Transaksi
        </h1>
        <p class="text-sm text-text-secondary mt-0.5">
            Tambah pemasukan atau pengeluaran baru
        </p>
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

    @if(!$shop)
        <div class="text-center py-12 text-text-secondary bg-bg-surface border border-border-base rounded-2xl p-6">
            <p class="mb-4">Anda harus membuat toko terlebih dahulu untuk mencatat transaksi.</p>
            <a href="{{ route('shop.create') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm transition-colors shadow">
                Buat Toko Sekarang
            </a>
        </div>
    @else
        <div class="max-w-3xl mx-auto">
            <div class="bg-bg-surface border border-border-base rounded-2xl overflow-hidden shadow-sm transition-colors duration-200">
                
                <!-- Tab Headers -->
                <div class="flex border-b border-border-base">
                    <button
                        type="button"
                        @click="activeTab = 'pemasukan'"
                        :class="activeTab === 'pemasukan' ? 'bg-green-600 text-white' : 'text-text-secondary hover:text-text-primary bg-bg-base/30'"
                        class="flex-1 py-4 text-sm font-bold tracking-widest uppercase transition-all flex items-center justify-center gap-2"
                    >
                        💰 Pemasukan
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'pengeluaran'"
                        :class="activeTab === 'pengeluaran' ? 'bg-red-600 text-white' : 'text-text-secondary hover:text-text-primary bg-bg-base/30'"
                        class="flex-1 py-4 text-sm font-bold tracking-widest uppercase transition-all flex items-center justify-center gap-2"
                    >
                        💸 Pengeluaran
                    </button>
                </div>

                <div class="p-6">
                    <!-- INCOME TAB FORM -->
                    <div x-show="activeTab === 'pemasukan'">
                        <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" 
                              @submit="if (payMethodPemasukan === 'nontunai' && !hasNonCashMethods) { alert('Silakan tambahkan metode pembayaran non-tunai di pengaturan terlebih dahulu.'); $event.preventDefault(); }">
                            @csrf
                            <input type="hidden" name="payment_method_type" :value="payMethodPemasukan" />

                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <!-- Col 1 -->
                                    <div class="space-y-4">
                                        <!-- Nominal -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Nominal (Rp)
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-text-secondary">Rp</span>
                                                <input
                                                    type="number"
                                                    name="amount"
                                                    step="0.01"
                                                    required
                                                    placeholder="0"
                                                    value="{{ old('amount') }}"
                                                    class="w-full pl-11 pr-4 py-3 text-xl font-bold text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none transition-all"
                                                    style="font-family: 'JetBrains Mono', monospace;"
                                                />
                                            </div>
                                        </div>

                                        <!-- Kategori -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Kategori
                                            </label>
                                            <div class="relative">
                                                <select
                                                    name="category_id"
                                                    required
                                                    class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none appearance-none transition-all"
                                                >
                                                    <option value="" class="bg-bg-surface text-text-primary">Pilih kategori…</option>
                                                    @foreach ($incomeCategories as $cat)
                                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                                                            {{ $cat->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none text-text-secondary" />
                                            </div>
                                        </div>

                                        <!-- Tanggal -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Tanggal Transaksi
                                            </label>
                                            <input
                                                type="date"
                                                name="transaction_date"
                                                required
                                                value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                                                class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none transition-all dark:color-scheme-dark"
                                            />
                                        </div>
                                    </div>

                                    <!-- Col 2: Deskripsi -->
                                    <div class="flex flex-col">
                                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                            Deskripsi / Catatan
                                        </label>
                                        <textarea
                                            name="description"
                                            placeholder="Contoh: Nasi goreng x3, es teh manis x2, kerupuk…"
                                            rows="9"
                                            class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none resize-none flex-1 transition-all"
                                        >{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-xs font-semibold mb-3 uppercase tracking-wider text-text-secondary">
                                        Metode Pembayaran
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            @click="payMethodPemasukan = 'tunai'"
                                            :class="payMethodPemasukan === 'tunai' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400' : 'border-border-base bg-bg-base/20 text-text-primary'"
                                            class="flex items-center gap-4 px-5 py-4 rounded-xl border-2 transition-all text-left"
                                        >
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                                 :class="payMethodPemasukan === 'tunai' ? 'border-emerald-600' : 'border-text-secondary'">
                                                <div x-show="payMethodPemasukan === 'tunai'" class="w-2 h-2 rounded-full bg-emerald-600"></div>
                                            </div>
                                            <span class="text-2xl">💵</span>
                                            <div>
                                                <p class="font-bold text-sm">Tunai</p>
                                                <p class="text-xs text-text-secondary">Uang tunai / cash</p>
                                            </div>
                                        </button>

                                        <button
                                            type="button"
                                            @click="payMethodPemasukan = 'nontunai'"
                                            :class="payMethodPemasukan === 'nontunai' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400' : 'border-border-base bg-bg-base/20 text-text-primary'"
                                            class="flex items-center gap-4 px-5 py-4 rounded-xl border-2 transition-all text-left"
                                        >
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                                 :class="payMethodPemasukan === 'nontunai' ? 'border-emerald-600' : 'border-text-secondary'">
                                                <div x-show="payMethodPemasukan === 'nontunai'" class="w-2 h-2 rounded-full bg-emerald-600"></div>
                                            </div>
                                            <span class="text-2xl">📱</span>
                                            <div>
                                                <p class="font-bold text-sm">Non-Tunai</p>
                                                <p class="text-xs text-text-secondary">Transfer, e-wallet, QRIS</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Dynamic Non-Cash Selector -->
                                <div x-show="payMethodPemasukan === 'nontunai'" style="display: none;" x-cloak>
                                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                        Pilih Akun QRIS / Bank
                                    </label>
                                    @if ($nonCashPaymentMethods->isEmpty())
                                        <div class="p-3 rounded-lg border border-warning-text/20 bg-warning-bg text-xs text-warning-text">
                                            ⚠️ Tidak ada metode pembayaran non-tunai yang aktif. Silakan tambahkan di pengaturan toko terlebih dahulu.
                                        </div>
                                    @else
                                        <div class="relative">
                                            <select
                                                name="payment_method_id"
                                                :required="payMethodPemasukan === 'nontunai'"
                                                class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none appearance-none transition-all"
                                            >
                                                <option value="" class="bg-bg-surface text-text-primary">Pilih Akun Pembayaran…</option>
                                                @foreach ($nonCashPaymentMethods as $pay)
                                                    <option value="{{ $pay->id }}" {{ old('payment_method_id') == $pay->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                                                        {{ $pay->name }} ({{ $pay->account_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none text-text-secondary" />
                                        </div>
                                    @endif
                                </div>

                                <!-- Proof Upload -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <label class="block text-xs font-semibold uppercase tracking-wider text-text-secondary">
                                            Unggah Bukti Transaksi
                                        </label>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-danger-bg text-danger-text border border-danger-text/20">Wajib</span>
                                    </div>
                                    
                                    <div class="rounded-xl transition-all cursor-pointer border-2 border-dashed p-8"
                                         :class="selectedFilePemasukan ? 'border-emerald-600 bg-emerald-50/10 dark:bg-emerald-950/5' : 'border-border-base bg-bg-base/20'">
                                        <div class="text-center">
                                            <x-lucide-upload class="w-6 h-6 mx-auto mb-2 text-emerald-600 dark:text-emerald-400" />
                                            <p class="font-semibold mb-1 text-text-primary text-sm" x-text="selectedFilePemasukan ? selectedFilePemasukan : 'Pilih file bukti transaksi'"></p>
                                            <label class="inline-block cursor-pointer text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                                klik untuk memilih file
                                                <input
                                                    type="file"
                                                    name="proof_image"
                                                    required
                                                    accept="image/*,application/pdf"
                                                    class="hidden"
                                                    @change="selectedFilePemasukan = $event.target.files[0] ? $event.target.files[0].name : null"
                                                />
                                            </label>
                                            <p class="text-xs mt-2 text-text-secondary">PNG, JPG, PDF — maks. 10 MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="flex items-center justify-between pt-4 border-t border-border-base">
                                    <p class="text-xs text-text-secondary">Semua kolom & bukti transaksi wajib diisi</p>
                                    <button
                                        type="submit"
                                        class="flex items-center gap-2 font-bold px-8 py-3.5 rounded-xl transition-all text-sm text-white bg-emerald-600 hover:bg-emerald-700 shadow-md"
                                    >
                                        <x-lucide-check class="w-4 h-4" />
                                        Simpan Transaksi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- EXPENSE TAB FORM -->
                    <div x-show="activeTab === 'pengeluaran'" style="display: none;" x-cloak>
                        <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data"
                              @submit="if (payMethodPengeluaran === 'nontunai' && !hasNonCashMethods) { alert('Silakan tambahkan metode pembayaran non-tunai di pengaturan terlebih dahulu.'); $event.preventDefault(); }">
                            @csrf
                            <input type="hidden" name="payment_method_type" :value="payMethodPengeluaran" />

                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <!-- Col 1 -->
                                    <div class="space-y-4">
                                        <!-- Nominal -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Nominal (Rp)
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-text-secondary">Rp</span>
                                                <input
                                                    type="number"
                                                    name="amount"
                                                    step="0.01"
                                                    required
                                                    placeholder="0"
                                                    value="{{ old('amount') }}"
                                                    class="w-full pl-11 pr-4 py-3 text-xl font-bold text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none transition-all"
                                                    style="font-family: 'JetBrains Mono', monospace;"
                                                />
                                            </div>
                                        </div>

                                        <!-- Kategori -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Kategori
                                            </label>
                                            <div class="relative">
                                                <select
                                                    name="category_id"
                                                    required
                                                    class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none appearance-none transition-all"
                                                >
                                                    <option value="" class="bg-bg-surface text-text-primary">Pilih kategori…</option>
                                                    @foreach ($expenseCategories as $cat)
                                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                                                            {{ $cat->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none text-text-secondary" />
                                            </div>
                                        </div>

                                        <!-- Tanggal -->
                                        <div>
                                            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                                Tanggal Transaksi
                                            </label>
                                            <input
                                                type="date"
                                                name="transaction_date"
                                                required
                                                value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                                                class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none transition-all dark:color-scheme-dark"
                                            />
                                        </div>
                                    </div>

                                    <!-- Col 2: Deskripsi -->
                                    <div class="flex flex-col">
                                        <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                            Deskripsi / Catatan
                                        </label>
                                        <textarea
                                            name="description"
                                            placeholder="Contoh: Pembelian bahan baku beras 50kg, bayar listrik..."
                                            rows="9"
                                            class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none resize-none flex-1 transition-all"
                                        >{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-xs font-semibold mb-3 uppercase tracking-wider text-text-secondary">
                                        Metode Pembayaran
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            @click="payMethodPengeluaran = 'tunai'"
                                            :class="payMethodPengeluaran === 'tunai' ? 'border-red-600 bg-red-50/50 dark:bg-red-950/20 text-red-600 dark:text-red-400' : 'border-border-base bg-bg-base/20 text-text-primary'"
                                            class="flex items-center gap-4 px-5 py-4 rounded-xl border-2 transition-all text-left"
                                        >
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                                 :class="payMethodPengeluaran === 'tunai' ? 'border-red-600' : 'border-text-secondary'">
                                                <div x-show="payMethodPengeluaran === 'tunai'" class="w-2 h-2 rounded-full bg-red-600"></div>
                                            </div>
                                            <span class="text-2xl">💵</span>
                                            <div>
                                                <p class="font-bold text-sm">Tunai</p>
                                                <p class="text-xs text-text-secondary">Uang tunai / cash</p>
                                            </div>
                                        </button>

                                        <button
                                            type="button"
                                            @click="payMethodPengeluaran = 'nontunai'"
                                            :class="payMethodPengeluaran === 'nontunai' ? 'border-red-600 bg-red-50/50 dark:bg-red-950/20 text-red-600 dark:text-red-400' : 'border-border-base bg-bg-base/20 text-text-primary'"
                                            class="flex items-center gap-4 px-5 py-4 rounded-xl border-2 transition-all text-left"
                                        >
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                                 :class="payMethodPengeluaran === 'nontunai' ? 'border-red-600' : 'border-text-secondary'">
                                                <div x-show="payMethodPengeluaran === 'nontunai'" class="w-2 h-2 rounded-full bg-red-600"></div>
                                            </div>
                                            <span class="text-2xl">📱</span>
                                            <div>
                                                <p class="font-bold text-sm">Non-Tunai</p>
                                                <p class="text-xs text-text-secondary">Transfer, e-wallet, QRIS</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Dynamic Non-Cash Selector -->
                                <div x-show="payMethodPengeluaran === 'nontunai'" style="display: none;" x-cloak>
                                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wider text-text-secondary">
                                        Pilih Akun QRIS / Bank
                                    </label>
                                    @if ($nonCashPaymentMethods->isEmpty())
                                        <div class="p-3 rounded-lg border border-warning-text/20 bg-warning-bg text-xs text-warning-text">
                                            ⚠️ Tidak ada metode pembayaran non-tunai yang aktif. Silakan tambahkan di pengaturan toko terlebih dahulu.
                                        </div>
                                    @else
                                        <div class="relative">
                                            <select
                                                name="payment_method_id"
                                                :required="payMethodPengeluaran === 'nontunai'"
                                                class="w-full px-4 py-3 text-sm text-text-primary bg-bg-base/50 border border-border-base rounded-xl focus:border-emerald-500 focus:ring-emerald-500 outline-none appearance-none transition-all"
                                            >
                                                <option value="" class="bg-bg-surface text-text-primary">Pilih Akun Pembayaran…</option>
                                                @foreach ($nonCashPaymentMethods as $pay)
                                                    <option value="{{ $pay->id }}" {{ old('payment_method_id') == $pay->id ? 'selected' : '' }} class="bg-bg-surface text-text-primary">
                                                        {{ $pay->name }} ({{ $pay->account_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none text-text-secondary" />
                                        </div>
                                    @endif
                                </div>

                                <!-- Proof Upload -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <label class="block text-xs font-semibold uppercase tracking-wider text-text-secondary">
                                            Unggah Bukti Transaksi
                                        </label>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-danger-bg text-danger-text border border-danger-text/20">Wajib</span>
                                    </div>
                                    
                                    <div class="rounded-xl transition-all cursor-pointer border-2 border-dashed p-8"
                                         :class="selectedFilePengeluaran ? 'border-red-600 bg-red-50/10 dark:bg-red-950/5' : 'border-border-base bg-bg-base/20'">
                                        <div class="text-center">
                                            <x-lucide-upload class="w-6 h-6 mx-auto mb-2 text-red-600 dark:text-red-400" />
                                            <p class="font-semibold mb-1 text-text-primary text-sm" x-text="selectedFilePengeluaran ? selectedFilePengeluaran : 'Pilih file bukti transaksi'"></p>
                                            <label class="inline-block cursor-pointer text-sm font-bold text-red-600 dark:text-red-400">
                                                klik untuk memilih file
                                                <input
                                                    type="file"
                                                    name="proof_image"
                                                    required
                                                    accept="image/*,application/pdf"
                                                    class="hidden"
                                                    @change="selectedFilePengeluaran = $event.target.files[0] ? $event.target.files[0].name : null"
                                                />
                                            </label>
                                            <p class="text-xs mt-2 text-text-secondary">PNG, JPG, PDF — maks. 10 MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="flex items-center justify-between pt-4 border-t border-border-base">
                                    <p class="text-xs text-text-secondary">Semua kolom & bukti transaksi wajib diisi</p>
                                    <button
                                        type="submit"
                                        class="flex items-center gap-2 font-bold px-8 py-3.5 rounded-xl transition-all text-sm text-white bg-red-600 hover:bg-red-700 shadow-md"
                                    >
                                        <x-lucide-check class="w-4 h-4" />
                                        Simpan Transaksi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
@endsection