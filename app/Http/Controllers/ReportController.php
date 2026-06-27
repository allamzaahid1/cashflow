<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

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
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'type' => $request->input('type', 'all'),
            'category_id' => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $categories = $shop->categories()->orderBy('name')->get();
        $paymentMethods = $shop->paymentMethods()->orderBy('name')->get();

        $transactions = $this->reportService->getPaginatedReport($shop, $filters, 10, (int) $request->input('page', 1));
        $summary = $this->reportService->getSummary($shop, $filters);

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
    public function exportPdf(Request $request): RedirectResponse|Response
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create');
        }

        $filters = [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'type' => $request->input('type', 'all'),
            'category_id' => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $transactions = $this->reportService->getReportItems($shop, $filters);
        $summary = $this->reportService->getSummary($shop, $filters);

        $pdf = Pdf::loadView('reports.pdf', compact('transactions', 'summary', 'filters', 'shop'));

        return $pdf->download('laporan-arus-kas-'.now()->format('YmdHis').'.pdf');
    }

    /**
     * Export the filtered transaction report as Excel.
     */
    public function exportExcel(Request $request): RedirectResponse|StreamedResponse
    {
        $shop = auth()->user()->shop;

        if (! $shop) {
            return redirect()
                ->route('shop.create');
        }

        $filters = [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->toDateString()),
            'type' => $request->input('type', 'all'),
            'category_id' => $request->input('category_id'),
            'payment_method_id' => $request->input('payment_method_id'),
        ];

        $transactions = $this->reportService->getReportItems($shop, $filters);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="laporan-arus-kas-'.now()->format('YmdHis').'.xlsx"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Tanggal', 'Kode Transaksi', 'Tipe Transaksi', 'Kategori', 'Metode Pembayaran', 'Keterangan', 'Pemasukan', 'Pengeluaran', 'Saldo Akhir']);

            foreach ($transactions as $tx) {
                fputcsv($file, [
                    Carbon::parse($tx->date)->format('d/m/Y'),
                    $tx->transaction_code ?: '-',
                    $tx->is_withdrawal ? 'Penarikan' : ($tx->type === 'income' ? 'Pemasukan' : 'Pengeluaran'),
                    $tx->category_name ?: '-',
                    $tx->payment_method_name ?: '-',
                    $tx->description ?: '-',
                    $tx->is_withdrawal ? '' : ($tx->type === 'income' ? number_format($tx->amount, 2, '.', '') : ''),
                    $tx->is_withdrawal ? number_format($tx->amount + $tx->admin_fee, 2, '.', '') : ($tx->type === 'expense' ? number_format($tx->amount, 2, '.', '') : ''),
                    number_format($tx->running_balance, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
