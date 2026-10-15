<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class InventoryReportController extends Controller
{
    public function export(Request $request, ReportService $service)
    {
        $filters = $service->makePeriod($request->only(['period', 'start', 'end']));
        $format = $request->input('format', 'excel');
        $section = $request->input('section', 'raw_material');

        if ($section === 'hpp_margin') {
            $rows = $this->buildHppMarginRows($service->getHppAndMargin());
            $title = 'HPP & Margin';
            $filename = 'hpp-margin-report';
        } elseif ($section === 'cashier_session') {
            $rows = $this->buildCashierSessionRows($service->getCashierSessionSummary($filters));
            $title = 'Rekap Sesi Kasir';
            $filename = 'cashier-session-report';
        } else {
            $rows = $this->buildRawMaterialUsageRows($service->getRawMaterialUsage($filters));
            $title = 'Pemakaian Bahan Baku';
            $filename = 'raw-material-usage-report';
        }

        if ($format === 'pdf') {
            return Pdf::loadView('admin.reports.pdf.inventory', [
                'filters' => $filters,
                'section' => $section,
                'rows' => $rows,
                'title' => $title,
            ])->download($filename . '.pdf');
        }

        $headings = $this->getHeadingsForSection($section);

        return Excel::download(new ArrayExport($rows, $headings), $filename . '.xlsx');
    }

    private function getHeadingsForSection(string $section): array
    {
        return match ($section) {
            'hpp_margin' => ['Produk', 'Ukuran', 'Harga Jual', 'HPP', 'Margin Rp', 'Margin %'],
            'cashier_session' => ['Sesi ID', 'Kasir', 'Dibuka', 'Ditutup', 'Saldo Awal', 'Saldo Akhir', 'Total Penjualan', 'Jumlah Pesanan', 'Selisih'],
            default => ['Bahan Baku', 'Qty Digunakan', 'Total Biaya', 'Biaya / Unit'],
        };
    }

    private function buildRawMaterialUsageRows(iterable $usage): array
    {
        $rows = [];

        foreach ($usage as $item) {
            $rows[] = [
                $item->raw_material->name ?? 'Unknown',
                $item->qty_used,
                $item->total_cost,
                $item->unit_cost,
            ];
        }

        return $rows;
    }

    private function buildHppMarginRows(iterable $rows): array
    {
        $export = [];

        foreach ($rows as $row) {
            $export[] = [
                $row->product_name,
                $row->size_variant,
                $row->sale_price,
                $row->hpp,
                $row->margin_rp,
                round($row->margin_pct, 2),
            ];
        }

        return $export;
    }

    private function buildCashierSessionRows(iterable $sessions): array
    {
        $rows = [];

        foreach ($sessions as $session) {
            $rows[] = [
                $session->id,
                $session->user_name,
                optional($session->opened_at)->format('Y-m-d H:i'),
                optional($session->closed_at)->format('Y-m-d H:i'),
                $session->opening_balance,
                $session->closing_balance,
                $session->total_sales,
                $session->orders_count,
                $session->difference,
            ];
        }

        return $rows;
    }
}
