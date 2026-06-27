<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {
    }

    /**
     * Display the filtered transaction report.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create')
                ->with('error', 'Anda harus membuat toko terlebih dahulu.');
        }

        // Set default filter properties if not set in query
        $filters = [
            'start_date'        => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date'          => $request->input('end_date', now()->toDateString()),
            'type'              => $request->input('type', 'all'),
            'category_id'       => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $categories = $shop->categories()->orderBy('name')->get();
        $paymentMethods = $shop->paymentMethods()->orderBy('name')->get();

        $query = $this->reportService->getTransactionQuery($shop, $filters);
        $summary = $this->reportService->getSummary($shop, $filters);

        // Paginate by 10 and maintain active query strings across page link updates
        $transactions = $query->paginate(10)->withQueryString();

        return view('reports.index', compact(
            'transactions',
            'summary',
            'categories',
            'paymentMethods',
            'filters',
            'shop'
        ));
    }

    /**
     * Export the filtered transaction report as PDF.
     */
    public function exportPdf(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create');
        }

        $filters = [
            'start_date'        => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date'          => $request->input('end_date', now()->toDateString()),
            'type'              => $request->input('type', 'all'),
            'category_id'       => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $transactions = $this->reportService->getTransactionQuery($shop, $filters)->get();
        $summary = $this->reportService->getSummary($shop, $filters);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', compact('transactions', 'summary', 'filters', 'shop'));

        return $pdf->download('laporan-arus-kas-' . now()->format('YmdHis') . '.pdf');
    }

    /**
     * Export the filtered transaction report as Excel.
     */
    public function exportExcel(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create');
        }

        $filters = [
            'start_date'        => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date'          => $request->input('end_date', now()->toDateString()),
            'type'              => $request->input('type', 'all'),
            'category_id'       => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $transactions = $this->reportService->getTransactionQuery($shop, $filters)->get();

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="laporan-arus-kas-' . now()->format('YmdHis') . '.xlsx"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Tanggal', 'Kode Transaksi', 'Kategori', 'Tipe', 'Metode Pembayaran', 'Keterangan', 'Jumlah']);

            foreach ($transactions as $tx) {
                $sign = $tx->category->type === 'income' ? '+' : '-';
                fputcsv($file, [
                    \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y'),
                    $tx->transaction_code,
                    $tx->category->name,
                    $tx->category->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $tx->paymentMethod->name,
                    $tx->description ?: '-',
                    $sign.number_format($tx->amount, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
