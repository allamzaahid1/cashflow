<!DOCTYPE html>
<html>
<head>
    <title>Laporan Arus Kas</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 20px; color: #111; }
        .header p { margin: 5px 0 0; color: #666; font-size: 13px; }
        .summary-box { margin-bottom: 25px; width: 100%; border-collapse: collapse; }
        .summary-box td { border: 1px solid #ddd; padding: 10px; text-align: center; width: 33%; }
        .summary-box .title { font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold; }
        .summary-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border-bottom: 1px solid #eee; padding: 8px 10px; text-align: left; }
        .table th { background-color: #f9f9f9; color: #555; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .table td { font-size: 11px; }
        .font-mono { font-family: Courier, monospace; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Arus Kas</h1>
        <p>{{ $shop->name }}</p>
        <p style="font-size: 11px;">Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="title">Total Kas Masuk</div>
                <div class="value text-green">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="title">Total Kas Keluar</div>
                <div class="value text-red">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="title">Saldo Akhir</div>
                <div class="value @if($summary['available_balance'] >= 0) text-green @else text-red @endif">
                    Rp {{ number_format($summary['available_balance'], 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Metode</th>
                <th>Keterangan</th>
                <th class="text-right">Pemasukan</th>
                <th class="text-right">Pengeluaran</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}</td>
                    <td class="font-mono">{{ $tx->transaction_code ?: '-' }}</td>
                    <td>
                        @if ($tx->is_withdrawal)
                            Penarikan
                        @else
                            {{ $tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                        @endif
                    </td>
                    <td>{{ $tx->category_name ?: '-' }}</td>
                    <td>{{ $tx->payment_method_name ?: '-' }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                    <td class="text-right font-mono text-green" style="font-weight: bold;">
                        @if (!$tx->is_withdrawal && $tx->type === 'income')
                            +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-mono text-red" style="font-weight: bold;">
                        @if ($tx->is_withdrawal)
                            -Rp {{ number_format($tx->amount + $tx->admin_fee, 0, ',', '.') }}
                        @elseif ($tx->type === 'expense')
                            -Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-mono" style="font-weight: bold;">
                        Rp {{ number_format($tx->running_balance, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #888;">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
