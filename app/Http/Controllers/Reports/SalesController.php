<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function export(Request $request, ReportService $service)
    {
        $filters = $service->makePeriod($request->only(['period', 'start', 'end']));
        $format = $request->input('format', 'excel');
        $summary = $service->getSalesSummary($filters);
        $topProducts = $service->getTopProducts($filters, 50);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.sales', [
                'filters' => $filters,
                'summary' => $summary,
                'topProducts' => $topProducts,
            ]);

            return $pdf->download('sales-report.pdf');
        }

        $rows = [];
        $rows[] = ['Sales Summary'];
        $rows[] = ['Revenue', $summary['total_revenue']];
        $rows[] = ['Transactions', $summary['transaction_count']];
        $rows[] = ['AOV', $summary['average_order_value']];
        $rows[] = ['Critical Stock Count', $summary['critical_stock_count']];
        $rows[] = [];
        $rows[] = ['Top Products'];
        $rows[] = ['Product', 'Quantity Sold', 'Revenue'];

        foreach ($topProducts as $product) {
            $rows[] = [
                $product->product->name ?? 'Unknown',
                (int) $product->quantity_sold,
                (float) $product->revenue,
            ];
        }

        return Excel::download(new ArrayExport($rows), 'sales-report.xlsx');
    }
}
