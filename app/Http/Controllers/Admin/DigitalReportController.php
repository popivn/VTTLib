<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DigitalResource;
use App\Models\DigitalFolder;
use App\Models\Branch;
use App\Models\ExportHistory;
use App\Exports\DynamicDigitalReportExport;
use App\Exports\DigitalQtyStatsExport;
use App\Jobs\ExportDigitalReportJob;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DigitalReportController extends Controller
{
    /**
     * Display the digital reports view.
     */
    public function index(Request $request)
    {
        $reportType = $request->query('report_type', 'digital_qty_stats');
        
        $folders = DigitalFolder::orderBy('sort_order')->orderBy('id')->get();
        $branches = Branch::where('is_active', true)->get();
        
        $reportsList = [
            'digital_qty_stats' => [
                'title' => __('Thống kê số lượng tài liệu số'),
                'desc' => __('Báo cáo tổng hợp số lượng tài liệu số phân chia chi tiết theo từng thư mục và thể loại tài liệu.')
            ],
            'digital_list' => [
                'title' => __('Danh sách tài liệu số trong thư viện'),
                'desc' => __('Báo cáo chi tiết danh sách tất cả các tài liệu số đang được quản lý trong thư viện.')
            ],
            'most_viewed_by_period' => [
                'title' => __('Danh sách tài liệu số xem nhiều theo thời gian'),
                'desc' => __('Danh sách các tài liệu số có lượt xem cao nhất lọc theo một khoảng thời gian cụ thể.')
            ],
            'most_downloaded_by_period' => [
                'title' => __('Danh sách tài liệu số tải nhiều theo thời gian'),
                'desc' => __('Danh sách các tài liệu số có lượt tải về cao nhất lọc theo một khoảng thời gian cụ thể.')
            ],
            'most_viewed' => [
                'title' => __('Danh sách tài liệu số xem nhiều'),
                'desc' => __('Danh sách các tài liệu số có tổng lượt xem trực tuyến cao nhất từ trước đến nay.')
            ],
            'most_downloaded' => [
                'title' => __('Danh sách tài liệu số tải nhiều'),
                'desc' => __('Danh sách các tài liệu số có tổng lượt tải về máy cao nhất từ trước đến nay.')
            ]
        ];

        if (!array_key_exists($reportType, $reportsList)) {
            $reportType = 'digital_qty_stats';
        }

        $activeReport = $reportsList[$reportType];
        
        return view('admin.digital-resources.reports.index', compact(
            'folders', 
            'branches', 
            'reportType',
            'activeReport',
            'reportsList'
        ));
    }

    /**
     * Build Data for Digital Quantity Statistics Report (grouped by folder)
     */
    public function buildQtyStatsData(Request $request): array
    {
        $folderIds = $request->input('folder_ids', []);
        $query = DigitalFolder::orderBy('sort_order')->orderBy('id');
        
        if (!empty($folderIds)) {
            $query->whereIn('id', $folderIds);
        }
        
        $folders = $query->get();
        $result = [];

        foreach ($folders as $folder) {
            $resQuery = DigitalResource::where('folder_id', $folder->id);
            
            if ($request->filled('date_from')) {
                $resQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $resQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }
            if ($request->filled('statuses')) {
                $statuses = (array) $request->input('statuses');
                $mapped = [];
                foreach ($statuses as $st) {
                    if ($st === 'active' || $st === 'published') {
                        $mapped[] = 'published';
                        $mapped[] = 'active';
                    } else {
                        $mapped[] = $st;
                    }
                }
                $resQuery->whereIn('status', array_unique($mapped));
            }

            $byType = (clone $resQuery)
                ->select('resource_type', DB::raw('count(*) as total'))
                ->groupBy('resource_type')
                ->get();

            $typesList = [];
            if ($byType->isEmpty()) {
                // If empty or no resource_type, fallback to format
                $byFormat = (clone $resQuery)
                    ->select('format', DB::raw('count(*) as total'))
                    ->groupBy('format')
                    ->get();

                foreach ($byFormat as $row) {
                    $name = !empty($row->format) ? strtoupper($row->format) . ' - Tài liệu số' : 'Tài liệu số';
                    $typesList[] = [
                        'name' => $name,
                        'count' => $row->total
                    ];
                }
            } else {
                foreach ($byType as $row) {
                    $name = !empty($row->resource_type) ? $row->resource_type : 'Tài liệu số';
                    $typesList[] = [
                        'name' => $name,
                        'count' => $row->total
                    ];
                }
            }

            $folderTotal = $resQuery->count();

            $result[] = [
                'folder_id' => $folder->id,
                'folder_name' => $folder->folder_name ?: ($folder->name ?: 'Thư mục tài liệu số'),
                'types' => $typesList,
                'total' => $folderTotal
            ];
        }

        return $result;
    }

    /**
     * Preview Report HTML via AJAX
     */
    public function preview(Request $request)
    {
        $reportType = $request->input('report_type', 'digital_qty_stats');
        
        if ($reportType === 'digital_qty_stats') {
            $foldersData = $this->buildQtyStatsData($request);
            $totalAllFolders = array_sum(array_column($foldersData, 'total'));

            return view('admin.digital-resources.reports.partials.preview_qty_stats', compact('foldersData', 'totalAllFolders'));
        }

        // Standard tabular report preview
        $data = $this->getReportData($request, $reportType);
        return view('admin.digital-resources.reports.partials.preview_table', [
            'headers' => $data['headers'],
            'rows' => array_slice($data['rows'], 0, 50),
            'title' => $data['title'],
            'totalCount' => count($data['rows'])
        ]);
    }

    /**
     * Generate Async Queue Export Job for Digital Reports
     */
    public function generate(Request $request)
    {
        $reportType = $request->input('report_type', 'digital_qty_stats');
        $format = $request->input('format', 'excel');

        $metaTitle = $reportType === 'digital_qty_stats' 
            ? 'Thống kê số lượng tài liệu số' 
            : 'Báo cáo tài liệu số';

        $fileName = ($reportType === 'digital_qty_stats' ? 'thong_ke_so_luong_tai_lieu_so' : 'digital_reports') . '_' . now()->format('Ymd_His');
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $fullFileName = $fileName . '.' . $extension;

        $history = ExportHistory::create([
            'user_id'     => auth()->id(),
            'report_type' => $reportType,
            'title'       => $metaTitle,
            'filename'    => $fullFileName,
            'format'      => $format,
            'status'      => 'pending',
            'progress'    => 0,
        ]);

        // Dispatch background queue job after response
        ExportDigitalReportJob::dispatch(
            $history->id,
            $request->all(),
            $reportType,
            $format
        )->afterResponse();

        return response()->json([
            'success' => true,
            'message' => __('Yêu cầu xuất báo cáo tài liệu số đã được tạo và đang xử lý dưới nền.'),
            'history' => $history
        ]);
    }

    /**
     * Build report rows and headers for non-qty-stats reports
     */
    public function getReportData(Request $request, string $reportType): array
    {
        $query = DigitalResource::with(['folder']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('publisher', 'LIKE', "%{$search}%");
            });
        }

        $folder_ids = $request->input('folder_ids', []);
        if (!empty($folder_ids)) {
            $query->whereIn('folder_id', $folder_ids);
        }

        $statuses = $request->input('statuses', []);
        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if (in_array($reportType, ['most_viewed', 'most_viewed_by_period'])) {
            $query->orderBy('view_count', 'desc');
        } elseif (in_array($reportType, ['most_downloaded', 'most_downloaded_by_period'])) {
            $query->orderBy('download_count', 'desc');
        } else {
            $query->latest();
        }

        $resources = $query->get();

        $headers = [__('STT'), __('Tiêu đề tài liệu'), __('Tác giả'), __('Nhà xuất bản'), __('Năm xuất bản'), __('Thư mục'), __('Ngôn ngữ'), __('Dung lượng (MB)')];
        $rows = [];

        foreach ($resources as $index => $r) {
            $sizeMb = round($r->file_size / (1024 * 1024), 2);
            $rows[] = [
                $index + 1,
                $r->title,
                is_array($r->authors) ? implode(', ', $r->authors) : $r->authors,
                $r->publisher,
                $r->publish_year,
                optional($r->folder)->name,
                $r->language,
                $sizeMb
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'title' => __('Danh sách tài liệu số trong thư viện')
        ];
    }

    /**
     * Get Export History API for polling
     */
    public function history()
    {
        $histories = ExportHistory::where('user_id', auth()->id())
            ->where('report_type', 'LIKE', '%digital%')
            ->orWhere('report_type', 'digital_qty_stats')
            ->latest()
            ->take(15)
            ->get();

        $unreadCount = ExportHistory::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'histories' => $histories,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Download completed export file from history
     */
    public function historyDownload($id)
    {
        $history = ExportHistory::where('user_id', auth()->id())->findOrFail($id);

        if ($history->status !== 'completed' || !$history->file_path) {
            return back()->with('error', __('File báo cáo chưa sẵn sàng để tải về.'));
        }

        if (!Storage::disk('local')->exists($history->file_path)) {
            return back()->with('error', __('Không tìm thấy file lưu trữ trên server.'));
        }

        $history->update(['is_read' => true]);
        return Storage::disk('local')->download($history->file_path, $history->filename);
    }

    /**
     * Delete export history item
     */
    public function historyDelete($id)
    {
        $history = ExportHistory::where('user_id', auth()->id())->findOrFail($id);
        if ($history->file_path && Storage::disk('local')->exists($history->file_path)) {
            Storage::disk('local')->delete($history->file_path);
        }
        $history->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Clear all completed history
     */
    public function clearHistory()
    {
        $histories = ExportHistory::where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'failed'])
            ->get();

        foreach ($histories as $h) {
            if ($h->file_path && Storage::disk('local')->exists($h->file_path)) {
                Storage::disk('local')->delete($h->file_path);
            }
            $h->delete();
        }

        return response()->json(['success' => true]);
    }
}
