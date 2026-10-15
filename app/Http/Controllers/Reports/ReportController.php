<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $filters = $service->makePeriod($request->only(['period', 'start', 'end']));
        $activeTab = $request->input('tab');

        if (! $activeTab) {
            $activeTab = $request->routeIs('admin.reports.inventory') ? 'raw_material' : 'sales';
        }

        return view('admin.reports.index', [
            'activeTab' => $activeTab,
            'filters' => $filters,
            'salesSummary' => $service->getSalesSummary($filters),
            'topProducts' => $service->getTopProducts($filters),
            'rawMaterialUsage' => $service->getRawMaterialUsage($filters),
            'hppMargin' => $service->getHppAndMargin(),
            'cashierSessions' => $service->getCashierSessionSummary($filters),
        ]);
    }
}
