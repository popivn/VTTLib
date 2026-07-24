<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarcFramework;
use App\Services\Marc\Iso2709ParserService;
use App\Services\Marc\MarcFrameworkService;
use App\Services\Marc\MarcImportService;
use App\Services\Marc\MarcLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarcImportController extends Controller
{
    protected Iso2709ParserService $parserService;
    protected MarcImportService $importService;
    protected MarcFrameworkService $frameworkService;
    protected MarcLabelService $labelService;

    public function __construct(
        Iso2709ParserService $parserService,
        MarcImportService $importService,
        MarcFrameworkService $frameworkService,
        MarcLabelService $labelService
    ) {
        $this->parserService = $parserService;
        $this->importService = $importService;
        $this->frameworkService = $frameworkService;
        $this->labelService = $labelService;
    }

    /**
     * Display MARC import page
     */
    public function index()
    {
        $frameworks = MarcFramework::where('is_active', true)->orderBy('is_default', 'desc')->get();
        return view('admin.marc-import.index', compact('frameworks'));
    }

    /**
     * Upload and parse MARC file (.mrc or .txt)
     */
    public function uploadMarcFile(Request $request)
    {
        $request->validate([
            'marc_file' => 'required|file|max:10240',
            'action_type' => 'required|in:create,update'
        ]);

        try {
            $file = $request->file('marc_file');
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, ['mrc', 'txt'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('Chỉ hỗ trợ file .mrc hoặc .txt')
                ], 422);
            }

            $rawContent = file_get_contents($file->getRealPath());
            $rawRecords = $this->parserService->splitRecords($rawContent);

            if (empty($rawRecords)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Không tìm thấy bản ghi MARC hợp lệ trong file')
                ], 422);
            }

            $parsedRecords = [];
            $errors = [];
            $allTags = [];

            foreach ($rawRecords as $index => $rawRecord) {
                $parsed = $this->parserService->parseRecord($rawRecord);
                if ($parsed) {
                    $parsed['row_index'] = $index + 1;
                    $parsedRecords[] = $parsed;

                    // Collect unique tags and subfields
                    foreach ($parsed['fields'] as $tag => $fieldDataArr) {
                        if (!isset($allTags[$tag])) {
                            $allTags[$tag] = [
                                'tag' => $tag,
                                'label' => $this->labelService->getTagLabel($tag),
                                'subfields' => []
                            ];
                        }
                        foreach ($fieldDataArr as $fieldData) {
                            if (isset($fieldData['subfields'])) {
                                foreach ($fieldData['subfields'] as $sf) {
                                    $allTags[$tag]['subfields'][$sf['code']] = $sf['code'];
                                }
                            }
                        }
                    }
                } else {
                    $errors[] = [
                        'row_index' => $index + 1,
                        'errors' => [__('Không thể phân tích bản ghi MARC')]
                    ];
                }
            }

            ksort($allTags);

            // Build preview (first 5 records)
            $preview = [];
            foreach (array_slice($parsedRecords, 0, 5) as $rec) {
                $preview[] = [
                    'row_index' => $rec['row_index'],
                    'title' => $rec['title'] ?? 'N/A',
                    'author' => $rec['author'] ?? 'N/A',
                    'isbn' => $rec['isbn'] ?? 'N/A',
                    'publisher' => $rec['publisher'] ?? 'N/A',
                    'year' => $rec['year'] ?? 'N/A',
                    'fields_summary' => $this->buildFieldsSummary($rec['fields'])
                ];
            }

            // Store in session for import
            session(['marc_import_data' => $parsedRecords]);
            session(['marc_import_action' => $request->action_type]);
            session(['marc_extracted_tags' => $allTags]);

            // Detect matching frameworks
            $matchingFrameworks = $this->frameworkService->detectMatchingFrameworks($allTags);

            return response()->json([
                'success' => true,
                'message' => __('File MARC đã được phân tích thành công'),
                'data' => [
                    'total_records' => count($rawRecords),
                    'valid_records' => count($parsedRecords),
                    'invalid_records' => count($errors),
                    'errors' => $errors,
                    'preview' => $preview,
                    'extracted_framework' => array_values($allTags),
                    'matching_frameworks' => $matchingFrameworks
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('MARC file upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Lỗi xử lý file: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process and import parsed MARC records into database
     */
    public function processMarcFile(Request $request)
    {
        $request->validate([
            'framework_id' => 'required|exists:marc_frameworks,id',
            'action_type' => 'required|in:create,update'
        ]);

        $parsedRecords = session('marc_import_data', []);

        if (empty($parsedRecords)) {
            return response()->json([
                'success' => false,
                'message' => __('Không tìm thấy dữ liệu đã phân tích. Vui lòng upload file lại.')
            ], 422);
        }

        try {
            $results = $this->importService->importParsedRecords(
                $request->framework_id,
                $request->action_type,
                $parsedRecords
            );

            session()->forget(['marc_import_data', 'marc_import_action', 'marc_extracted_tags']);

            return response()->json([
                'success' => true,
                'message' => __('Import hoàn tất'),
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('MARC file process error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Import thất bại: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save extracted framework from MARC file
     */
    public function saveFrameworkFromMarc(Request $request)
    {
        $request->validate([
            'framework_name' => 'required|string|max:255',
            'framework_code' => 'required|string|max:20|unique:marc_frameworks,code',
            'description' => 'nullable|string|max:1000'
        ]);

        $allTags = session('marc_extracted_tags', []);

        if (empty($allTags)) {
            return response()->json([
                'success' => false,
                'message' => __('Không có dữ liệu khung biên mục từ file. Vui lòng upload file lại.')
            ], 422);
        }

        try {
            $framework = $this->frameworkService->saveFrameworkFromExtractedData(
                $request->framework_name,
                $request->framework_code,
                $request->description,
                $allTags
            );

            return response()->json([
                'success' => true,
                'message' => __('Khung biên mục đã được lưu thành công'),
                'data' => [
                    'framework_id' => $framework->id,
                    'framework_name' => $framework->name,
                    'framework_code' => $framework->code
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Save framework from MARC error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Tiêu đề (*)', 'Tác giả', 'ISBN', 'Nhà xuất bản', 'Năm xuất bản',
                'Nơi xuất bản', 'Số trang', 'Kích thước', 'Giá tiền', 'Phân loại DDC',
                'Số ĐKCB', 'Tóm tắt nội dung', 'Ngôn ngữ'
            ]);
            fputcsv($file, [
                'Lập trình Laravel căn bản', 'Nguyễn Văn A', '9786044500089',
                'Thông tin và Truyền thông', '2025', 'Hà Nội', '350 tr.', '24 cm',
                '150000', '005.133', 'DKCB001', 'Sách hướng dẫn lập trình Web với Laravel Framework', 'vie'
            ]);
            fclose($file);
        }, 'mau_nhap_lieu_sach.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build summary of MARC fields for preview
     */
    protected function buildFieldsSummary(array $fields): array
    {
        $summary = [];
        foreach ($fields as $tag => $fieldDataArr) {
            $subfieldValues = [];
            foreach ($fieldDataArr as $fieldData) {
                if (isset($fieldData['subfields'])) {
                    foreach ($fieldData['subfields'] as $sf) {
                        if ($sf['code'] !== '_') {
                            $subfieldValues[] = '$' . $sf['code'] . ' ' . $sf['value'];
                        } else {
                            $subfieldValues[] = $sf['value'];
                        }
                    }
                }
            }

            $summary[] = [
                'tag' => $tag,
                'label' => $this->labelService->getTagLabel($tag),
                'value' => implode(' | ', $subfieldValues)
            ];
        }

        return $summary;
    }
}
