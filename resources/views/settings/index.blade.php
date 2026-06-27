@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div x-data="{ 
    activeTab: '{{ $errors->has('account_name') || $errors->has('account_number') || $errors->has('qr_image') || $errors->has('payment_method') ? 'pembayaran' : ($errors->any() ? 'kategori' : 'profil') }}', 
    showAddModal: false, 
    showEditModal: false, 
    editCategory: { id: '', name: '', type: '' },
    showAddPayModal: false,
    showEditPayModal: false,
    editPayMethod: { id: '', name: '', type: '', account_name: '', account_number: '', qr_image: '', is_active: true }
}">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-text-primary" style="font-family: 'Plus Jakarta Sans', sans-serif;">
            Pengaturan Toko
        </h1>
        <p class="text-sm text-text-secondary mt-0.5">Kelola informasi dan preferensi toko Anda</p>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="mb-6 rounded-lg bg-success-bg p-4 text-sm text-success-text border border-success-text/20">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-danger-bg p-4 text-sm text-danger-text border border-danger-text/20">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex gap-6">
        <!-- Vertical Tabs -->
        <div class="w-52 flex-shrink-0">
            <nav class="bg-bg-surface rounded-xl border border-border-base shadow-sm overflow-hidden transition-colors duration-200">
                <!-- Profil Toko Tab Button -->
                <button 
                    @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-success-bg text-success-text font-semibold border-l-2 border-l-emerald-600' : 'text-text-secondary hover:bg-bg-base/50 hover:text-text-primary'"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm text-left transition-all border-b border-border-base cursor-pointer"
                >
                    <x-lucide-store class="w-4 h-4 flex-shrink-0" />
                    Profil Toko
                </button>

                <!-- Kategori Toko Tab Button -->
                <button 
                    @click="activeTab = 'kategori'"
                    :class="activeTab === 'kategori' ? 'bg-success-bg text-success-text font-semibold border-l-2 border-l-emerald-600' : 'text-text-secondary hover:bg-bg-base/50 hover:text-text-primary'"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm text-left transition-all border-b border-border-base cursor-pointer"
                >
                    <x-lucide-tag class="w-4 h-4 flex-shrink-0" />
                    Kategori Toko
                </button>

                <!-- Metode Pembayaran Tab Button -->
                <button 
                    @click="activeTab = 'pembayaran'"
                    :class="activeTab === 'pembayaran' ? 'bg-success-bg text-success-text font-semibold border-l-2 border-l-emerald-600' : 'text-text-secondary hover:bg-bg-base/50 hover:text-text-primary'"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm text-left transition-all border-b border-border-base cursor-pointer"
                >
                    <x-lucide-qr-code class="w-4 h-4 flex-shrink-0" />
                    Metode Pembayaran
                </button>

                <!-- Keamanan Tab Button -->
                <button 
                    @click="activeTab = 'keamanan'"
                    :class="activeTab === 'keamanan' ? 'bg-success-bg text-success-text font-semibold border-l-2 border-l-emerald-600' : 'text-text-secondary hover:bg-bg-base/50 hover:text-text-primary'"
                    class="w-full flex items-center gap-3 px-4 py-3.5 text-sm text-left transition-all last:border-0 cursor-pointer"
                >
                    <x-lucide-shield class="w-4 h-4 flex-shrink-0" />
                    Keamanan
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="flex-1">
            <!-- Profil Toko Tab -->
            <div x-show="activeTab === 'profil'" class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-6">
                <h2 class="font-semibold text-text-primary mb-5">Informasi Profil Toko</h2>
                <div class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Toko</label>
                        <input
                            type="text"
                            value="{{ $shop->name ?? '' }}"
                            disabled
                            class="w-full px-4 py-2.5 text-sm text-text-secondary bg-bg-base border border-border-base rounded-lg outline-none cursor-not-allowed"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Alamat</label>
                        <textarea
                            rows="3"
                            disabled
                            class="w-full px-4 py-2.5 text-sm text-text-secondary bg-bg-base border border-border-base rounded-lg outline-none cursor-not-allowed resize-none"
                        >{{ $shop->address ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Kontak</label>
                        <input
                            type="text"
                            value="{{ $shop->phone ?? '' }}"
                            disabled
                            class="w-full px-4 py-2.5 text-sm text-text-secondary bg-bg-base border border-border-base rounded-lg outline-none cursor-not-allowed"
                        />
                    </div>
                </div>
            </div>

            <!-- Kategori Toko Tab -->
            <div x-show="activeTab === 'kategori'" class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-6" x-cloak style="display: none;">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="font-semibold text-text-primary text-lg">Kategori Arus Kas</h2>
                        <p class="text-xs text-text-secondary mt-0.5">Kelola kategori pemasukan dan pengeluaran toko Anda</p>
                    </div>
                    @if($shop)
                    <button
                        @click="showAddModal = true"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm cursor-pointer"
                    >
                        <x-lucide-plus class="w-4 h-4" />
                        Tambah Kategori
                    </button>
                    @endif
                </div>

                @if(!$shop)
                    <div class="text-center py-10 text-text-secondary text-sm">
                        Anda harus membuat toko terlebih dahulu sebelum mengelola kategori.
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Pemasukan (Income) List -->
                        <div class="border border-border-base rounded-xl p-4 bg-bg-base/30">
                            <div class="flex items-center gap-2 mb-4 border-b border-border-base pb-2">
                                <span class="text-success-text">💵</span>
                                <h3 class="font-bold text-text-primary text-sm uppercase tracking-wider">Pemasukan (Income)</h3>
                            </div>
                            <div class="space-y-2">
                                @forelse ($categories->where('type', 'income') as $cat)
                                    <div class="flex items-center justify-between p-3 rounded-lg border border-border-base bg-bg-surface shadow-sm">
                                        <span class="font-medium text-text-primary text-sm">{{ $cat->name }}</span>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                @click="showEditModal = true; editCategory = { id: '{{ $cat->id }}', name: '{{ addslashes($cat->name) }}', type: 'income' }"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors cursor-pointer"
                                            >
                                                <x-lucide-edit-2 class="w-3.5 h-3.5" />
                                            </button>
                                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-danger-text hover:border-danger-text/30 hover:bg-danger-bg/50 transition-colors cursor-pointer"
                                                >
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-text-secondary text-xs">Belum ada kategori pemasukan.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Pengeluaran (Expense) List -->
                        <div class="border border-border-base rounded-xl p-4 bg-bg-base/30">
                            <div class="flex items-center gap-2 mb-4 border-b border-border-base pb-2">
                                <span class="text-danger-text">💸</span>
                                <h3 class="font-bold text-text-primary text-sm uppercase tracking-wider">Pengeluaran (Expense)</h3>
                            </div>
                            <div class="space-y-2">
                                @forelse ($categories->where('type', 'expense') as $cat)
                                    <div class="flex items-center justify-between p-3 rounded-lg border border-border-base bg-bg-surface shadow-sm">
                                        <span class="font-medium text-text-primary text-sm">{{ $cat->name }}</span>
                                        <div class="flex items-center gap-2">
                                            <button 
                                                @click="showEditModal = true; editCategory = { id: '{{ $cat->id }}', name: '{{ addslashes($cat->name) }}', type: 'expense' }"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors cursor-pointer"
                                            >
                                                <x-lucide-edit-2 class="w-3.5 h-3.5" />
                                            </button>
                                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-danger-text hover:border-danger-text/30 hover:bg-danger-bg/50 transition-colors cursor-pointer"
                                                >
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-text-secondary text-xs">Belum ada kategori pengeluaran.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Metode Pembayaran Tab -->
            <div x-show="activeTab === 'pembayaran'" class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-6" x-cloak style="display: none;">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="font-semibold text-text-primary text-lg">Daftar Akun Penerima & QRIS</h2>
                        <p class="text-xs text-text-secondary mt-0.5">Kelola metode pembayaran tunai dan non-tunai untuk toko Anda</p>
                    </div>
                    @if($shop)
                    <button
                        @click="showAddPayModal = true"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm cursor-pointer"
                    >
                        <x-lucide-plus class="w-4 h-4" />
                        Tambah Akun QRIS/Bank
                    </button>
                    @endif
                </div>

                @if(!$shop)
                    <div class="text-center py-10 text-text-secondary text-sm">
                        Anda harus membuat toko terlebih dahulu sebelum mengelola metode pembayaran.
                    </div>
                @else
                    <div class="space-y-4">
                        <!-- Cash (Tunai) - Default & Read-only -->
                        @foreach ($paymentMethods->where('type', 'cash') as $pay)
                            <div class="flex items-center gap-4 p-4 rounded-xl border border-success-text/20 bg-success-bg/25">
                                <div class="w-14 h-14 flex-shrink-0 rounded-lg bg-bg-surface border border-border-base flex items-center justify-center text-2xl">
                                    💵
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-text-primary text-sm">{{ $pay->name }}</p>
                                    <p class="text-xs text-text-secondary mt-0.5">Transaksi langsung menggunakan uang fisik</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-bg text-success-text border border-success-text/20">
                                        Aktif Selamanya
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <!-- Custom Non-Cash Payment Methods -->
                        <div class="space-y-3 pt-2">
                            <h3 class="text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">Metode Non-Tunai</h3>
                            @forelse ($paymentMethods->where('type', '!=', 'cash') as $pay)
                                <div class="flex items-center gap-4 p-4 rounded-xl border border-border-base hover:border-border-base bg-bg-base/30 transition-colors">
                                    <!-- QR thumbnail or Type Icon -->
                                    <div class="w-14 h-14 flex-shrink-0 rounded-lg bg-bg-surface border border-border-base flex items-center justify-center p-1.5 overflow-hidden">
                                        @if($pay->qr_image)
                                            <a href="{{ asset('storage/' . $pay->qr_image) }}" target="_blank" title="Lihat QR Code">
                                                <img src="{{ asset('storage/' . $pay->qr_image) }}" class="w-full h-full object-cover rounded" />
                                            </a>
                                        @else
                                            <span class="text-xl">
                                                @if($pay->type === 'qris') 📱 @elseif($pay->type === 'transfer') 💳 @else 📱 @endif
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-text-primary text-sm">{{ $pay->name }}</p>
                                            <span class="text-xs uppercase px-2 py-0.5 rounded bg-bg-base text-text-secondary font-semibold">
                                                {{ $pay->type }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-text-secondary mt-1">
                                            A/N: <span class="font-medium text-text-primary">{{ $pay->account_name }}</span> &bull; 
                                            No: <span class="font-mono text-text-primary">{{ $pay->account_number }}</span>
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <!-- Active Status Toggle Form -->
                                        <form action="{{ route('payment-methods.toggle', $pay) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="{{ $pay->is_active ? 'true' : 'false' }} ? 'bg-emerald-600' : 'bg-border-base'"
                                            >
                                                <span 
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-bg-surface shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="{{ $pay->is_active ? 'true' : 'false' }} ? 'translate-x-5' : 'translate-x-0'"
                                                ></span>
                                            </button>
                                        </form>

                                        <!-- Edit/Delete Action Buttons -->
                                        <div class="flex items-center gap-2">
                                            <button 
                                                @click="showEditPayModal = true; editPayMethod = { id: '{{ $pay->id }}', name: '{{ addslashes($pay->name) }}', type: '{{ $pay->type }}', account_name: '{{ addslashes($pay->account_name) }}', account_number: '{{ $pay->account_number }}', qr_image: '{{ $pay->qr_image }}', is_active: {{ $pay->is_active ? 'true' : 'false' }} }"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-950/20 transition-colors cursor-pointer"
                                            >
                                                <x-lucide-edit-2 class="w-3.5 h-3.5" />
                                            </button>
                                            <form action="{{ route('payment-methods.destroy', $pay) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus metode pembayaran ini?')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-border-base text-text-secondary hover:text-danger-text hover:border-danger-text/30 hover:bg-danger-bg/50 transition-colors cursor-pointer"
                                                >
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-text-secondary text-xs">Belum ada metode pembayaran non-tunai.</div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            <!-- Keamanan Tab -->
            <div x-show="activeTab === 'keamanan'" class="bg-bg-surface rounded-xl border border-border-base shadow-sm p-6" x-cloak style="display: none;">
                <h2 class="font-semibold text-text-primary mb-5">Keamanan Akun</h2>
                <div class="text-text-secondary text-sm">Fitur ini belum dikonfigurasi.</div>
            </div>
        </div>
    </div>

    <template x-teleport="#floating-layer">
        <div x-show="showAddModal" class="fixed inset-0 z-70 flex items-center justify-center pointer-events-none" style="display: none;" x-cloak>
            <div class="absolute inset-0 z-60 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="showAddModal = false"></div>
            <div class="relative z-70 bg-bg-surface border border-border-base text-text-primary rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border-base">
                    <h3 class="font-bold text-text-primary text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Tambah Kategori Baru
                    </h3>
                    <button
                        type="button"
                        @click="showAddModal = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-bg-base transition-colors text-text-secondary"
                    >
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Kategori</label>
                            <input
                                name="name"
                                required
                                placeholder="cth: Penjualan Tambahan, Listrik"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tipe Kategori</label>
                            <div class="relative">
                                <select 
                                    name="type" 
                                    required
                                    class="w-full appearance-none px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                                >
                                    <option value="income" class="bg-bg-surface text-text-primary">Pemasukan (Income)</option>
                                    <option value="expense" class="bg-bg-surface text-text-primary">Pengeluaran (Expense)</option>
                                </select>
                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary pointer-events-none" />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 px-6 py-4 border-t border-border-base">
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-text-secondary border border-border-base rounded-lg hover:bg-bg-base transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer">
                            Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="#floating-layer">
        <div x-show="showEditModal" class="fixed inset-0 z-70 flex items-center justify-center pointer-events-none" style="display: none;" x-cloak>
            <div class="absolute inset-0 z-60 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="showEditModal = false"></div>
            <div class="relative z-70 bg-bg-surface border border-border-base text-text-primary rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border-base">
                    <h3 class="font-bold text-text-primary text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Ubah Kategori
                    </h3>
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-bg-base transition-colors text-text-secondary"
                    >
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>
                <form :action="'/categories/' + editCategory.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Kategori</label>
                            <input
                                name="name"
                                required
                                x-model="editCategory.name"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tipe Kategori</label>
                            <div class="relative">
                                <select 
                                    name="type" 
                                    required
                                    x-model="editCategory.type"
                                    class="w-full appearance-none px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                                >
                                    <option value="income" class="bg-bg-surface text-text-primary">Pemasukan (Income)</option>
                                    <option value="expense" class="bg-bg-surface text-text-primary">Pengeluaran (Expense)</option>
                                </select>
                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary pointer-events-none" />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 px-6 py-4 border-t border-border-base">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-text-secondary border border-border-base rounded-lg hover:bg-bg-base transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="#floating-layer">
        <div x-show="showAddPayModal" class="fixed inset-0 z-70 flex items-center justify-center pointer-events-none" style="display: none;" x-cloak>
            <div class="absolute inset-0 z-60 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="showAddPayModal = false"></div>
            <div class="relative z-70 bg-bg-surface border border-border-base text-text-primary rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden pointer-events-auto" x-data="{ payType: 'qris' }">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border-base">
                    <h3 class="font-bold text-text-primary text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Tambah Akun QRIS/Bank
                    </h3>
                    <button
                        type="button"
                        @click="showAddPayModal = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-bg-base transition-colors text-text-secondary"
                    >
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>
                <form action="{{ route('payment-methods.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Akun</label>
                            <input
                                name="name"
                                required
                                placeholder="cth: Gopay Warung, BCA Kasir"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tipe Akun</label>
                            <div class="relative">
                                <select 
                                    name="type" 
                                    required
                                    x-model="payType"
                                    class="w-full appearance-none px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                                >
                                    <option value="qris" class="bg-bg-surface text-text-primary">QRIS</option>
                                    <option value="transfer" class="bg-bg-surface text-text-primary">Transfer Bank</option>
                                    <option value="ewallet" class="bg-bg-surface text-text-primary">E-wallet (Gopay/OVO/DANA)</option>
                                </select>
                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary pointer-events-none" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Pemilik Rekening / Akun</label>
                            <input
                                name="account_name"
                                required
                                placeholder="cth: Budi Santoso"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nomor Telepon / Rekening</label>
                            <input
                                name="account_number"
                                required
                                placeholder="cth: 0812xxxxxxxx atau 1234567890"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div x-show="payType === 'qris'">
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Upload File QR Code</label>
                            <div class="border-2 border-dashed border-border-base rounded-xl p-4 text-center bg-bg-base/30 text-text-secondary">
                                <x-lucide-qr-code class="w-8 h-8 text-text-secondary/40 mx-auto mb-2" />
                                <label class="cursor-pointer text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    Pilih file QR Code
                                    <input type="file" name="qr_image" accept="image/*" class="hidden" />
                                </label>
                                <p class="text-xs text-text-secondary mt-1">PNG, JPG — maks. 2 MB</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 px-6 py-4 border-t border-border-base">
                        <button
                            type="button"
                            @click="showAddPayModal = false"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-text-secondary border border-border-base rounded-lg hover:bg-bg-base transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer">
                            Simpan Metode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="#floating-layer">
        <div x-show="showEditPayModal" class="fixed inset-0 z-70 flex items-center justify-center pointer-events-none" style="display: none;" x-cloak>
            <div class="absolute inset-0 z-60 bg-black/40 backdrop-blur-sm pointer-events-auto" @click="showEditPayModal = false"></div>
            <div class="relative z-70 bg-bg-surface border border-border-base text-text-primary rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-border-base">
                    <h3 class="font-bold text-text-primary text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Ubah Akun QRIS/Bank
                    </h3>
                    <button
                        type="button"
                        @click="showEditPayModal = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-bg-base transition-colors text-text-secondary"
                    >
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>
                <form :action="'/payment-methods/' + editPayMethod.id" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Akun</label>
                            <input
                                name="name"
                                required
                                x-model="editPayMethod.name"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Tipe Akun</label>
                            <div class="relative">
                                <select 
                                    name="type" 
                                    required
                                    x-model="editPayMethod.type"
                                    class="w-full appearance-none px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                                >
                                    <option value="qris" class="bg-bg-surface text-text-primary">QRIS</option>
                                    <option value="transfer" class="bg-bg-surface text-text-primary">Transfer Bank</option>
                                    <option value="ewallet" class="bg-bg-surface text-text-primary">E-wallet (Gopay/OVO/DANA)</option>
                                </select>
                                <x-lucide-chevron-down class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-secondary pointer-events-none" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nama Pemilik Rekening / Akun</label>
                            <input
                                name="account_name"
                                required
                                x-model="editPayMethod.account_name"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Nomor Telepon / Rekening</label>
                            <input
                                name="account_number"
                                required
                                x-model="editPayMethod.account_number"
                                class="w-full px-4 py-2.5 text-sm text-text-primary bg-bg-base/30 border border-border-base rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors"
                            />
                        </div>
                        <div x-show="editPayMethod.type === 'qris'">
                            <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">Upload File QR Code Baru (Opsional)</label>
                            <div class="border-2 border-dashed border-border-base rounded-xl p-4 text-center bg-bg-base/30 text-text-secondary">
                                <x-lucide-qr-code class="w-8 h-8 text-text-secondary/40 mx-auto mb-2" />
                                <label class="cursor-pointer text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    Pilih file QR Code baru
                                    <input type="file" name="qr_image" accept="image/*" class="hidden" />
                                </label>
                                <p class="text-xs text-text-secondary mt-1">PNG, JPG — maks. 2 MB (Kosongkan jika tidak diubah)</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 px-6 py-4 border-t border-border-base">
                        <button
                            type="button"
                            @click="showEditPayModal = false"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-text-secondary border border-border-base rounded-lg hover:bg-bg-base transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection