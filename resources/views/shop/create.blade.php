@extends('layouts.app')

@section('title', 'Buat Toko')

@section('content')

<div class="max-w-2xl rounded-xl bg-bg-surface border border-border-base text-text-primary p-8 shadow-sm">

    <h1 class="mb-6 text-2xl font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">

        Lengkapi Data Toko

    </h1>

    <form method="POST"
          action="{{ route('shop.store') }}"
          enctype="multipart/form-data">

        @csrf

        <div class="space-y-4">

            <div>

                <label class="mb-2 block text-xs font-semibold text-text-secondary uppercase tracking-wider">

                    Nama Toko

                </label>

                <input
                    name="name"
                    required
                    class="w-full rounded-lg border border-border-base bg-bg-base/30 text-text-primary p-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors">

            </div>

            <div>

                <label class="mb-2 block text-xs font-semibold text-text-secondary uppercase tracking-wider">

                    Nomor HP

                </label>

                <input
                    name="phone"
                    required
                    class="w-full rounded-lg border border-border-base bg-bg-base/30 text-text-primary p-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors">

            </div>

            <div>

                <label class="mb-2 block text-xs font-semibold text-text-secondary uppercase tracking-wider">

                    Alamat

                </label>

                <textarea
                    name="address"
                    required
                    rows="4"
                    class="w-full rounded-lg border border-border-base bg-bg-base/30 text-text-primary p-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500 transition-colors resize-none"></textarea>

            </div>

            <button
                class="rounded-lg bg-emerald-600 hover:bg-emerald-700 font-bold px-5 py-3 text-white transition-colors cursor-pointer">

                Simpan

            </button>

        </div>

    </form>

</div>

@endsection