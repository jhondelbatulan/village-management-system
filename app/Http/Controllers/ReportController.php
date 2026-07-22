<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Models\Village;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug ?? 'resident';
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $categories = $this->reportService->getReportsByCategory();
        $allowedTypes = $this->reportService->getAllowedReportTypes($role);
        $recentReports = GeneratedReport::where('user_id', $user->id)->latest()->limit(5)->with('village')->get();

        $filteredCategories = [];
        foreach ($categories as $catKey => $cat) {
            $filtered = array_filter($cat['reports'], fn($key) => in_array($key, $allowedTypes), ARRAY_FILTER_USE_KEY);
            if (!empty($filtered)) {
                $filteredCategories[$catKey] = ['label' => $cat['label'], 'reports' => $filtered];
            }
        }

        return view('reports.index', compact('villages', 'categories', 'filteredCategories', 'recentReports', 'allowedTypes'));
    }

    public function generate(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'report_type' => 'required|string',
            'format' => 'required|in:pdf,csv,excel',
            'village_id' => 'nullable|exists:villages,id',
        ]);

        $reportType = $request->input('report_type');
        $format = $request->input('format');
        $villageId = $request->input('village_id');
        $role = auth()->user()->role?->role_slug ?? 'resident';

        if (!$this->reportService->canAccessReport($role, $reportType)) {
            abort(403, 'You do not have permission to generate this report.');
        }

        $filters = $request->only([
            'date_from', 'date_to', 'gender', 'civil_status', 'age_group',
            'occupation', 'employment_status', 'education', 'income_range',
            'block', 'lot', 'household_id',
        ]);

        $data = $this->reportService->getReportData($reportType, $villageId, $filters);

        $record = $this->reportService->recordGeneration($reportType, $villageId, $format, $filters);

        return match ($format) {
            'pdf' => $this->exportPdf($reportType, $data, $villageId, $record),
            'csv' => $this->exportCsv($reportType, $data, $record),
            'excel' => $this->exportExcel($reportType, $data, $record),
            default => redirect()->route('reports.index')->with('error', 'Unsupported format.'),
        };
    }

    public function history(): View
    {
        $reports = $this->reportService->getReportHistory(auth()->id());
        return view('reports.history', compact('reports'));
    }

    public function printView(Request $request): View
    {
        $reportType = $request->input('report_type', '');
        $villageId = $request->input('village_id');
        $role = auth()->user()->role?->role_slug ?? 'resident';

        if (!$this->reportService->canAccessReport($role, $reportType)) {
            abort(403);
        }

        $filters = $request->only([
            'date_from', 'date_to', 'gender', 'civil_status', 'age_group',
            'occupation', 'employment_status', 'education', 'income_range',
        ]);

        $data = $this->reportService->getReportData($reportType, $villageId, $filters);

        return view('reports.print', $data);
    }

    public function schedule(Request $request): View
    {
        $this->authorizeSchedule();
        $scheduledReports = $this->reportService->getScheduledReports();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $reportTypes = $this->reportService->getReportTypes();
        return view('reports.schedule', compact('scheduledReports', 'villages', 'reportTypes'));
    }

    public function storeSchedule(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSchedule();
        $request->validate([
            'report_type' => 'required|string',
            'village_id' => 'nullable|exists:villages,id',
            'frequency' => 'required|in:weekly,monthly,quarterly,annually',
        ]);

        $info = ReportService::REPORT_TYPES[$request->report_type] ?? ['name' => $request->report_type, 'category' => 'misc'];
        GeneratedReport::create([
            'user_id' => auth()->id(),
            'report_name' => $info['name'] . ' (Scheduled)',
            'report_type' => $request->report_type,
            'category' => $info['category'],
            'village_id' => $request->village_id,
            'format' => 'pdf',
            'is_scheduled' => true,
            'schedule_frequency' => $request->frequency,
            'next_run_at' => $this->getNextRunDate($request->frequency),
            'is_active' => true,
        ]);

        return redirect()->route('reports.schedule')->with('success', 'Report scheduled successfully.');
    }

    public function destroySchedule(GeneratedReport $report): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSchedule();
        $report->delete();
        return redirect()->route('reports.schedule')->with('success', 'Schedule removed.');
    }

    protected function authorizeSchedule(): void
    {
        $role = auth()->user()->role?->role_slug ?? '';
        if (!in_array($role, ['super_admin', 'admin'])) {
            abort(403, 'Only Super Admin and Admin can manage report schedules.');
        }
    }

    protected function getNextRunDate(string $frequency): \Carbon\Carbon
    {
        return match ($frequency) {
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addQuarter(),
            'annually' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    protected function exportPdf(string $reportType, array $data, ?int $villageId, GeneratedReport $record): \Symfony\Component\HttpFoundation\Response
    {
        $view = "reports.exports.{$reportType}";
        if (!view()->exists($view)) {
            $view = 'reports.exports.generic-pdf';
        }

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $village = $villageId ? Village::find($villageId) : null;
        $filename = $reportType . ($village ? "-{$village->village_code}" : "-all") . "-" . now()->format('Y-m-d') . ".pdf";
        $path = storage_path("app/reports/{$filename}");

        if (!is_dir(storage_path('app/reports'))) {
            mkdir(storage_path('app/reports'), 0755, true);
        }

        $pdf->save($path);
        $record->update(['file_path' => $path, 'file_size' => filesize($path)]);

        return $pdf->download($filename);
    }

    protected function exportCsv(string $reportType, array $data, GeneratedReport $record): \Symfony\Component\HttpFoundation\Response
    {
        $filename = $reportType . "-" . now()->format('Y-m-d-His') . ".csv";
        $rows = $data['rows'] ?? [];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($data, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$data['reportTitle'] ?? 'Report']);
            fputcsv($handle, ['Village: ' . ($data['villageName'] ?? 'All')]);
            fputcsv($handle, ['Generated: ' . ($data['generatedAt'] ?? '')]);
            fputcsv($handle, ['By: ' . ($data['generatedBy'] ?? '')]);
            fputcsv($handle, []);

            if (!empty($rows)) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row));
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function exportExcel(string $reportType, array $data, GeneratedReport $record): \Symfony\Component\HttpFoundation\Response
    {
        $filename = $reportType . "-" . now()->format('Y-m-d-His') . ".xls";

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Report</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><td colspan="10" style="font-size:14pt;font-weight:bold;">' . htmlspecialchars($data['reportTitle'] ?? $reportType) . '</td></tr>';
        $html .= '<tr><td>Village:</td><td colspan="9">' . htmlspecialchars($data['villageName'] ?? 'All') . '</td></tr>';
        $html .= '<tr><td>Generated:</td><td colspan="9">' . htmlspecialchars($data['generatedAt'] ?? '') . '</td></tr>';
        $html .= '<tr><td>By:</td><td colspan="9">' . htmlspecialchars($data['generatedBy'] ?? '') . '</td></tr>';
        $html .= '<tr></tr>';

        $rows = $data['rows'] ?? [];
        if (!empty($rows)) {
            $html .= '<tr>';
            foreach (array_keys($rows[0]) as $h) {
                $html .= '<th style="background-color:#2e59d9;color:white;font-weight:bold;">' . htmlspecialchars($h) . '</th>';
            }
            $html .= '</tr>';
            $i = 0;
            foreach ($rows as $row) {
                $bg = $i % 2 === 0 ? '#f8f9fc' : '#ffffff';
                $html .= '<tr style="background-color:' . $bg . ';">';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars((string) ($cell ?? '')) . '</td>';
                }
                $html .= '</tr>';
                $i++;
            }
        }

        $html .= '</table></body></html>';

        $record->update(['file_size' => strlen($html)]);

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
