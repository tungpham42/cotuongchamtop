<?php

namespace App\Http\Controllers;

use App\Services\GoogleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminMarketingController extends Controller
{
    /**
     * Preset ranges shown as quick-select buttons on the dashboard.
     */
    protected array $presets = [
        '7d' => 7,
        '28d' => 28,
        '90d' => 90,
    ];

    public function index(Request $request)
    {
        [$startDate, $endDate, $preset] = $this->resolveRange($request);

        $report = app(GoogleAnalyticsService::class)->fullReport($startDate, $endDate);

        return view('admin.marketing', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'preset' => $preset,
            'presets' => array_keys($this->presets),
        ]);
    }

    /**
     * AJAX endpoint used by the date-range picker to refresh charts without
     * a full page reload.
     */
    public function data(Request $request): JsonResponse
    {
        [$startDate, $endDate] = $this->resolveRange($request);

        $report = app(GoogleAnalyticsService::class)->fullReport($startDate, $endDate);

        return response()->json($report);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function resolveRange(Request $request): array
    {
        $preset = $request->input('range', '28d');

        if ($request->filled('start') && $request->filled('end')) {
            $start = Carbon::parse($request->input('start'))->format('Y-m-d');
            $end = Carbon::parse($request->input('end'))->format('Y-m-d');

            return [$start, $end, 'custom'];
        }

        $days = $this->presets[$preset] ?? $this->presets['28d'];

        $start = Carbon::today()->subDays($days - 1)->format('Y-m-d');
        $end = Carbon::today()->format('Y-m-d');

        return [$start, $end, $preset];
    }
}
