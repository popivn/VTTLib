<?php

namespace App\Jobs;

use App\Models\ExportHistory;
use App\Models\DigitalFolder;
use App\Models\DigitalResource;
use App\Http\Controllers\Admin\DigitalReportController;
use App\Exports\DigitalQtyStatsExport;
use App\Exports\DynamicDigitalReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExportDigitalReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $historyId;
    protected $requestData;
    protected $reportType;
    protected $format;

    /**
     * Create a new job instance.
     */
    public function __construct(int $historyId, array $requestData, string $reportType, string $format)
    {
        $this->historyId   = $historyId;
        $this->requestData = $requestData;
        $this->reportType  = $reportType;
        $this->format      = $format;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $history = ExportHistory::find($this->historyId);
        if (!$history) {
            return;
        }

        $startTime = microtime(true);

        try {
            $history->update(['status' => 'processing', 'progress' => 10]);

            $request = Request::create('/admin/digital-reports/generate', 'POST', $this->requestData);
            $controller = app(DigitalReportController::class);

            $fileName = ($this->reportType === 'digital_qty_stats' ? 'thong_ke_so_luong_tai_lieu_so' : 'digital_reports') . '_' . now()->format('Ymd_His');
            $extension = $this->format === 'csv' ? 'csv' : 'xlsx';
            $relativeFilePath = 'exports/' . $fileName . '.' . $extension;

            if (!Storage::disk('local')->exists('exports')) {
                Storage::disk('local')->makeDirectory('exports');
            }

            $absolutePath = Storage::disk('local')->path($relativeFilePath);

            if ($this->reportType === 'digital_qty_stats') {
                $foldersData = $controller->buildQtyStatsData($request);
                Excel::store(
                    new DigitalQtyStatsExport($foldersData, 'THỐNG KÊ SỐ LƯỢNG TÀI LIỆU SỐ TRONG THƯ VIỆN'),
                    $relativeFilePath,
                    'local',
                    $this->format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
                );
            } else {
                $meta = $controller->getReportData($request, $this->reportType);
                Excel::store(
                    new DynamicDigitalReportExport($meta['headers'], $meta['rows'], $meta['title']),
                    $relativeFilePath,
                    'local',
                    $this->format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
                );
            }

            $history->update(['progress' => 90]);
            $executionTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            $history->update([
                'status'            => 'completed',
                'progress'          => 100,
                'file_path'         => $relativeFilePath,
                'execution_time_ms' => $executionTimeMs,
            ]);

            Log::info("ExportDigitalReportJob completed successfully. History ID: {$this->historyId}");
        } catch (\Throwable $e) {
            Log::error("ExportDigitalReportJob failed. History ID: {$this->historyId}. Error: " . $e->getMessage());

            $currentHistory = ExportHistory::find($this->historyId);
            if ($currentHistory) {
                $currentHistory->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $currentHistory = ExportHistory::find($this->historyId);
        if ($currentHistory) {
            $currentHistory->update([
                'status'        => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
