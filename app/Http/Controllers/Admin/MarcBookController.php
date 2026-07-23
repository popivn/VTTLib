<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BibliographicRecord;
use App\Models\MarcTagDefinition;
use App\Models\MarcFramework;
use App\Models\DocumentType;
use App\Models\StorageLocation;
use App\Models\BookItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcBookController extends Controller
{
    protected $barcodeService;

    public function __construct(\App\Services\BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    public function index(Request $request)
    {
        $query = BibliographicRecord::with(['fields.subfields', 'items', 'documentType']);

        // Advanced search filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                // Search in title (245$a)
                $q->whereHas('fields', function ($fieldQuery) use ($searchTerm) {
                    $fieldQuery->where('tag', '245')
                        ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                            $subfieldQuery->where('code', 'a')
                                ->where('value', 'like', '%' . $searchTerm . '%');
                        });
                })
                    // Search in author (100$a)
                    ->orWhereHas('fields', function ($fieldQuery) use ($searchTerm) {
                        $fieldQuery->where('tag', '100')
                            ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                                $subfieldQuery->where('code', 'a')
                                    ->where('value', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    // Search in ISBN (020$a)
                    ->orWhereHas('fields', function ($fieldQuery) use ($searchTerm) {
                        $fieldQuery->where('tag', '020')
                            ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                                $subfieldQuery->where('code', 'a')
                                    ->where('value', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    // Search in publisher (260$b)
                    ->orWhereHas('fields', function ($fieldQuery) use ($searchTerm) {
                        $fieldQuery->where('tag', '260')
                            ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                                $subfieldQuery->where('code', 'b')
                                    ->where('value', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    // Search in subject (650$a)
                    ->orWhereHas('fields', function ($fieldQuery) use ($searchTerm) {
                        $fieldQuery->where('tag', '650')
                            ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                                $subfieldQuery->where('code', 'a')
                                    ->where('value', 'like', '%' . $searchTerm . '%');
                            });
                    })
                    // Search in notes (500$a)
                    ->orWhereHas('fields', function ($fieldQuery) use ($searchTerm) {
                        $fieldQuery->where('tag', '500')
                            ->whereHas('subfields', function ($subfieldQuery) use ($searchTerm) {
                                $subfieldQuery->where('code', 'a')
                                    ->where('value', 'like', '%' . $searchTerm . '%');
                            });
                    });
            });
        }

        // Filter by framework
        if ($request->filled('framework')) {
            $query->where('framework', $request->framework);
        }

        // Filter by record type
        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject category
        if ($request->filled('subject_category')) {
            $query->where('subject_category', $request->subject_category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by specific MARC tag
        if ($request->filled('marc_tag') && $request->filled('marc_value')) {
            $query->whereHas('fields', function ($fieldQuery) use ($request) {
                $fieldQuery->where('tag', $request->marc_tag)
                    ->whereHas('subfields', function ($subfieldQuery) use ($request) {
                        $subfieldQuery->where('value', 'like', '%' . $request->marc_value . '%');
                    });
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'title':
                $query->orderBy(function ($q) {
                    $q->selectRaw('COALESCE(
                        (SELECT value FROM marc_subfields 
                         JOIN marc_fields ON marc_subfields.marc_field_id = marc_fields.id 
                         WHERE marc_fields.record_id = bibliographic_records.id 
                         AND marc_fields.tag = "245" AND marc_subfields.code = "a" 
                         LIMIT 1), "") as title');
                }, $sortOrder);
                break;
            case 'author':
                $query->orderBy(function ($q) {
                    $q->selectRaw('COALESCE(
                        (SELECT value FROM marc_subfields 
                         JOIN marc_fields ON marc_subfields.marc_field_id = marc_fields.id 
                         WHERE marc_fields.record_id = bibliographic_records.id 
                         AND marc_fields.tag = "100" AND marc_subfields.code = "a" 
                         LIMIT 1), "") as author');
                }, $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $records = $query->paginate(10)->withQueryString();

        // Get search filters data
        $frameworks = MarcFramework::where('is_active', true)->pluck('code', 'code');
        $recordTypes = BibliographicRecord::distinct()->pluck('record_type');
        $subjectCategories = BibliographicRecord::distinct()->pluck('subject_category');
        $commonMarcTags = ['245', '100', '020', '260', '650', '500', '082', '852'];

        return view('admin.marc_books.index', compact(
            'records',
            'frameworks',
            'recordTypes',
            'subjectCategories',
            'commonMarcTags'
        ));
    }

    public function form(Request $request, ?BibliographicRecord $record = null)
    {
        if ($record) {
            $record->load(['fields.subfields', 'items']);
        }

        $frameworks = MarcFramework::where('is_active', true)->get();
        $requestedFrameworkId = $request->query('framework_id');

        $frameworkId = $requestedFrameworkId
            ?: ($record ? optional($frameworks->firstWhere('code', $record->framework))->id : null);

        if (!$frameworkId && !$record && $frameworks->isNotEmpty()) {
            $standard = $frameworks->where('code', 'STANDARD')->first();
            $frameworkId = $standard ? $standard->id : $frameworks->first()->id;
        }

        $currentFramework = MarcFramework::find($frameworkId);

        if ($record) {
            // SNAPSHOT LOGIC: Ưu tiên lấy cấu trúc từ chính bản ghi
            // 1. Lấy tất cả các Tags hiện có trong bản ghi này
            $recordFields = $record->fields()->with('subfields')->orderBy('tag')->get();
            $recordTags = $recordFields->pluck('tag')->unique();
            
            // Append requested add_tag if present
            if ($request->filled('add_tag')) {
                $recordTags->push($request->add_tag);
                $recordTags = $recordTags->unique();
            }

            // 2. Lấy định nghĩa chuẩn cho các Tags này để lấy nhãn (label) và mô tả
            $definitions = MarcTagDefinition::whereIn('tag', $recordTags)
                ->with(['subfields' => function ($q) {
                    $q->orderBy('code');
                }])
                ->get()
                ->map(function($def) use ($recordFields) {
                    // Đối với mỗi tag hiện có, chúng ta cũng cần đảm bảo các subfield thực tế đang có dữ liệu được hiển thị
                    $actualField = $recordFields->firstWhere('tag', $def->tag);
                    if ($actualField) {
                        $actualCodes = $actualField->subfields->pluck('code')->unique();
                        
                        // Hợp nhất: lấy định nghĩa chuẩn cộng với bất kỳ subfield nào thực tế đang có
                        // Điều này cho phép hỗ trợ cả các trường tùy chỉnh (custom subfields)
                        $mergedSubfields = $def->subfields->filter(function($s) use ($actualCodes) {
                            return $actualCodes->contains($s->code) || $s->is_visible;
                        });
                        
                        // Nếu có subfield thực tế mà trong định nghĩa chuẩn không có (trường hợp hiếm)
                        $standardCodes = $def->subfields->pluck('code');
                        foreach ($actualCodes as $code) {
                            if (!$standardCodes->contains($code)) {
                                $mergedSubfields->push((object)[
                                    'code' => $code,
                                    'label' => "Subfield $code",
                                    'is_visible' => true
                                ]);
                            }
                        }
                        $def->setRelation('subfields', $mergedSubfields->sortBy('code')->values());
                    }
                    return $def;
                });

            // Nếu muốn vẫn hiển thị các trường "trống" từ Framework nhưng chưa có dữ liệu trong Record
            // (Tùy chọn: Nếu bạn muốn Snapshot sạch hoàn toàn thì bỏ qua bước này)
            if ($currentFramework) {
                $frameworkTags = $currentFramework->tags()
                    ->with(['subfields' => function ($q) {
                        $q->where('is_visible', true)->orderBy('code');
                    }])
                    ->wherePivot('is_visible', true)
                    ->get();

                $definitionsByTag = $definitions->keyBy('tag');
                foreach ($frameworkTags as $fTag) {
                    if (!$definitionsByTag->has($fTag->tag)) {
                        $definitions->push($fTag);
                    }
                }
                
                // Đảm bảo không có tag nào bị lặp lại và sắp xếp theo số tag
                $definitions = $definitions->unique('tag')->sortBy('tag')->values();
            }
        } else {
            // CREATE MODE: Chỉ hiển thị các tag/subfield theo Framework mẫu
            $definitions = $currentFramework ? $currentFramework->tags()
                ->with(['subfields' => function ($q) {
                    $q->where('is_visible', true)->orderBy('code');
                }])
                ->wherePivot('is_visible', true)
                ->get() : collect();

            // Append requested add_tag if present in create mode
            if ($request->filled('add_tag')) {
                $extraTag = MarcTagDefinition::where('tag', $request->add_tag)
                    ->with(['subfields' => function ($q) {
                        $q->where('is_visible', true)->orderBy('code');
                    }])
                    ->first();
                if ($extraTag && !$definitions->contains('tag', $request->add_tag)) {
                    $definitions->push($extraTag);
                    $definitions = $definitions->sortBy('tag')->values();
                }
            }
        }

        $documentTypes = DocumentType::active()->ordered()->get();
        $locations = StorageLocation::where('is_active', true)->with('branch')->get();
        $branches = \App\Models\Branch::with('storageLocations')->where('is_active', true)->get();
        $bibliographicLevels = \App\Models\BibliographicLevel::active()->ordered()->get();
        $nextBarcode = $this->barcodeService->previewNextBarcode('item');

        if ($record) {
            $record->load('items.branch', 'items.storageLocation');
            return view('admin.marc_books.form', compact('record', 'definitions', 'frameworks', 'documentTypes', 'locations', 'frameworkId', 'branches', 'bibliographicLevels', 'nextBarcode'))->with('barcodeService', $this->barcodeService);
        }

        // Khi không có record, vẫn truyền $record = null để view có thể kiểm tra
        $record = null;
        return view('admin.marc_books.form', compact('record', 'definitions', 'documentTypes', 'locations', 'frameworks', 'frameworkId', 'branches', 'bibliographicLevels', 'nextBarcode'))->with('barcodeService', $this->barcodeService);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Handle cover image upload
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')->store('covers', 'public');
            }

            // Create bibliographic record with metadata
            $metadata = $this->getMetadataWithNames($request);
            $record = BibliographicRecord::create(array_merge([
                'leader' => '00000nam a2200000 i 4500',
                'cover_image' => $coverImagePath,
                'framework' => $request->input('framework', 'STANDARD'),
                'status' => $request->input('status', BibliographicRecord::STATUS_PENDING),
                'is_featured' => $request->boolean('is_featured'),
                'document_type_id' => $request->input('document_type_id'),
                'subject_category' => $request->input('subject_category'),
            ], $metadata));

            // Process MARC fields
            $fields = $request->input('fields', []);
            $sequence = 0;

            foreach ($fields as $tag => $data) {
                $subfieldEntries = $data['subfields'] ?? [];
                $hasData = false;
                foreach ($subfieldEntries as $entry) {
                    if (!empty($entry['code']) && !empty($entry['value'])) {
                        $hasData = true;
                        break;
                    }
                }

                if (!$hasData) continue;

                // Sử dụng updateOrCreate thay vì create để đảm bảo tính duy nhất của Tag
                $marcField = $record->fields()->updateOrCreate(
                    ['tag' => $tag],
                    [
                        'indicator1' => $data['ind1'] ?? ' ',
                        'indicator2' => $data['ind2'] ?? ' ',
                        'sequence' => $sequence++
                    ]
                );

                foreach ($subfieldEntries as $entry) {
                    if (!empty($entry['code']) && !empty($entry['value'])) {
                        $marcField->subfields()->create([
                            'code' => substr($entry['code'], 0, 1),
                            'value' => $entry['value']
                        ]);
                    }
                }
            }

            // Process distribution items
            $items = $request->input('items', []);
            foreach ($items as $itemData) {
                if (!empty($itemData['storage_location_id'])) {
                    $itemPayload = [
                        'bibliographic_record_id' => $record->id,
                        'branch_id' => $itemData['branch_id'] ?? null,
                        'storage_location_id' => $itemData['storage_location_id'],
                        'barcode' => $itemData['barcode'] ?? null,
                        'accession_number' => $itemData['accession_number'] ?? $this->generateAccessionNumber(),
                        'storage_type' => $itemData['storage_type'] ?? 'Book',
                        'quantity' => $itemData['quantity'] ?? 1,
                        'status' => $itemData['status'] ?? 'available',
                        'order_code' => $itemData['order_code'] ?? null,
                        'waits_for_print' => isset($itemData['waits_for_print']) ? (bool)$itemData['waits_for_print'] : false,
                        'notes' => $itemData['notes'] ?? null,
                        'volume_issue' => $itemData['volume_issue'] ?? null,
                        'day' => $itemData['day'] ?? null,
                        'month_season' => $itemData['month_season'] ?? null,
                        'year' => $itemData['year'] ?? null,
                        'shelf' => $itemData['shelf'] ?? null,
                        'shelf_position' => $itemData['shelf_position'] ?? null,
                        'location' => $itemData['location'] ?? null,
                        'temporary_location' => $itemData['temporary_location'] ?? null,
                    ];

                    if (empty($itemPayload['barcode'])) {
                        $itemPayload['barcode'] = $this->barcodeService->getNextCode('item');
                        $this->barcodeService->incrementCounter('item', $itemPayload['barcode']);
                    }

                    $newItem = BookItem::create($itemPayload);
                    
                    // Save barcode as SVG file
                    $this->barcodeService->saveAsFile(
                        $newItem->barcode, 
                        'items/barcodes/' . $newItem->barcode . '.svg'
                    );
                }
            }

            DB::commit();
            $tab = $request->input('tab', 0);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Đã lưu bản ghi thành công'),
                    'redirect' => route('admin.marc.book.form', ['record' => $record->id, 'tab' => $tab]),
                    'record_id' => $record->id,
                ]);
            }

            return redirect()->route('admin.marc.book.form', ['record' => $record->id, 'tab' => $tab])->with('success', __('Đã lưu bản ghi thành công'));
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Lỗi khi biên mục: ') . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', __('Lỗi khi biên mục: ') . $e->getMessage())->withInput();
        }
    }

    private function getMetadataWithNames(Request $request)
    {
        $metadata = [
            'record_type' => $request->record_type,
            'bibliographic_level' => $request->bibliographic_level,
            'serial_frequency' => $request->serial_frequency,
            'date_type' => $request->date_type,
            'acquisition_method' => $request->acquisition_method,
            'document_format' => $request->document_format,
            'cataloging_standard' => $request->cataloging_standard,
        ];

        $mappings = [
            'status' => [
                'pending' => ['vi' => 'Mới', 'en' => 'New'],
                'approved' => ['vi' => 'Đã duyệt', 'en' => 'Approved'],
            ],
            'record_type' => [
                'book' => ['vi' => 'Sách', 'en' => 'Books'],
                'article' => ['vi' => 'Bài trích', 'en' => 'Article'],
                'collection' => ['vi' => 'Bộ sưu tập', 'en' => 'Collection'],
                'file' => ['vi' => 'Tập tin', 'en' => 'Computer file'],
                'address' => ['vi' => 'Địa chỉ', 'en' => 'Address'],
                'map' => ['vi' => 'Bản đồ', 'en' => 'Maps'],
                'mixed' => ['vi' => 'Tài liệu hỗn hợp', 'en' => 'Mixed Material'],
                'audio' => ['vi' => 'Âm thanh', 'en' => 'Music'],
                'journal' => ['vi' => 'Ấn phẩm định kỳ', 'en' => 'Serials'],
                'digital' => ['vi' => 'Số hóa', 'en' => 'Digitization'],
                'resource' => ['vi' => 'Tài liệu số', 'en' => 'Digital Resource'],
                'video' => ['vi' => 'Video', 'en' => 'Video'],
                'visual' => ['vi' => 'Thiết bị, vật thể', 'en' => 'Visual Material'],
            ],
            'bibliographic_level' => [
                'a' => ['vi' => 'Tài liệu văn bản', 'en' => 'Language material'],
                'c' => ['vi' => 'Bản nhạc in', 'en' => 'Notated music'],
                'd' => ['vi' => 'Bản nhạc chép tay', 'en' => 'Manuscript notated music'],
                'e' => ['vi' => 'Bản đồ in', 'en' => 'Cartographic material'],
                'f' => ['vi' => 'Bản đồ vẽ tay', 'en' => 'Manuscript cartographic material'],
                'g' => ['vi' => 'Các tư liệu chiếu', 'en' => 'Projected medium'],
                'i' => ['vi' => 'Ghi âm không thuộc âm nhạc', 'en' => 'Nonmusical sound recording'],
                'j' => ['vi' => 'Ghi âm thuộc âm nhạc', 'en' => 'Musical sound recording'],
                'k' => ['vi' => 'Đồ họa phẳng', 'en' => 'Two-dimensional nonprojectable graphic'],
                'm' => ['vi' => 'Tập tin máy tính', 'en' => 'Computer file'],
                'o' => ['vi' => 'Bộ tài liệu', 'en' => 'Kit'],
                'p' => ['vi' => 'Tài liệu hỗn hợp', 'en' => 'Mixed material'],
                'r' => ['vi' => 'Đồ vật 3 chiều', 'en' => '3-D object'],
                't' => ['vi' => 'Tài liệu viết tay', 'en' => 'Manuscript language material'],
            ],
            'serial_frequency' => [
                'unknown' => ['vi' => 'Không xác định', 'en' => 'No determinable frequency'],
                'a' => ['vi' => 'Hàng năm', 'en' => 'Annual'],
                'b' => ['vi' => 'Hai tháng/kỳ', 'en' => 'Bimonthly'],
                'c' => ['vi' => 'Hai kỳ/tuần', 'en' => 'Semiweekly'],
                'd' => ['vi' => 'Nhật báo', 'en' => 'Daily'],
                'e' => ['vi' => 'Hai tuần/kỳ', 'en' => 'Biweekly'],
                'f' => ['vi' => 'Hai kỳ/năm', 'en' => 'Semiannual'],
                'g' => ['vi' => 'Hai năm/kỳ', 'en' => 'Biennial'],
                'h' => ['vi' => 'Ba năm/kỳ', 'en' => 'Triennial'],
                'i' => ['vi' => 'Ba kỳ/tuần', 'en' => 'Three times a week'],
                'j' => ['vi' => 'Ba kỳ/tháng', 'en' => 'Three times a month'],
                'm' => ['vi' => 'Báo tháng', 'en' => 'Monthly'],
                'q' => ['vi' => 'Báo quý', 'en' => 'Quarterly'],
                's' => ['vi' => 'Hai kỳ/tháng', 'en' => 'Semimonthly'],
                't' => ['vi' => 'Ba kỳ/năm', 'en' => 'Three times a year'],
                'u' => ['vi' => 'Không biết', 'en' => 'Unknown'],
                'w' => ['vi' => 'Tuần báo', 'en' => 'Weekly'],
                'z' => ['vi' => 'Khác', 'en' => 'Other'],
            ],
            'date_type' => [
                'bc' => ['vi' => 'No dates given; B.C. date involved', 'en' => 'No dates given; B.C. date involved'],
                'c' => ['vi' => 'Continuing resource currently published', 'en' => 'Continuing resource currently published'],
                'd' => ['vi' => 'Continuing resource ceased publication', 'en' => 'Continuing resource ceased publication'],
                'e' => ['vi' => 'Detailed date', 'en' => 'Detailed date'],
                'i' => ['vi' => 'Inclusive dates of collection', 'en' => 'Inclusive dates of collection'],
                'k' => ['vi' => 'Range of years of bulk of collection', 'en' => 'Range of years of bulk of collection'],
                'm' => ['vi' => 'Multiple dates', 'en' => 'Multiple dates'],
                'n' => ['vi' => 'Dates unknown', 'en' => 'Dates unknown'],
                'p' => ['vi' => 'Date of distribution/release/issue and production/recording session when different', 'en' => 'Date of distribution/release/issue and production/recording session when different'],
                'q' => ['vi' => 'Questionable date', 'en' => 'Questionable date'],
                'r' => ['vi' => 'Reprint/reissue date and original date', 'en' => 'Reprint/reissue date and original date'],
                's' => ['vi' => 'Single known date/probable date', 'en' => 'Single known date/probable date'],
                't' => ['vi' => 'Publication date and copyright date', 'en' => 'Publication date and copyright date'],
                'u' => ['vi' => 'Continuing resource status unknown', 'en' => 'Continuing resource status unknown'],
            ],
            'acquisition_method' => [
                'vol_date' => ['vi' => 'Vol.# MM/DD/YYYY', 'en' => 'Vol.# MM/DD/YYYY'],
                'untraced' => ['vi' => 'Ấn phẩm không theo dõi', 'en' => 'Untraced serials'],
                'date' => ['vi' => 'MM/DD/YYYY', 'en' => 'MM/DD/YYYY'],
                'month_year' => ['vi' => 'MM, YYYY', 'en' => 'MM, YYYY'],
                'season_year' => ['vi' => 'Season, YYYY', 'en' => 'Season, YYYY'],
                'year' => ['vi' => 'YYYY', 'en' => 'YYYY'],
                'vol' => ['vi' => 'Vol.#', 'en' => 'Vol.#'],
                'vol_month_year' => ['vi' => 'Vol.# MM, YYYY', 'en' => 'Vol.# MM, YYYY'],
                'vol_year' => ['vi' => 'Vol.# YYYY', 'en' => 'Vol.# YYYY'],
                'vol_season_year' => ['vi' => 'Vol.# Season, YYYY', 'en' => 'Vol.# Season, YYYY'],
                'other' => ['vi' => 'Khác', 'en' => 'Other'],
            ],
            'document_format' => [
                'none' => ['vi' => 'Không có trong các loại sau', 'en' => 'None of the following'],
                'a' => ['vi' => 'Vi phim', 'en' => 'Microfilm'],
                'b' => ['vi' => 'Vi phiếu', 'en' => 'Microfiche'],
                'c' => ['vi' => 'Vi phiếu mờ', 'en' => 'Microopaque'],
                'f' => ['vi' => 'Chữ in lớn', 'en' => 'Large print'],
                'g' => ['vi' => 'Chữ nổi', 'en' => 'Braille'],
                'r' => ['vi' => 'Bản sao, bản in thông thường', 'en' => 'Regular print reproduction'],
                's' => ['vi' => 'Điện tử', 'en' => 'Electronic'],
            ],
            'cataloging_standard' => [
                'AACR2' => ['vi' => 'AACR-2', 'en' => 'AACR-2'],
                'ISBD' => ['vi' => 'ISBD', 'en' => 'ISBD'],
            ],
        ];

        foreach ($mappings as $field => $options) {
            $val = $request->input($field);
            if ($val && isset($options[$val])) {
                $metadata["{$field}_vi"] = $options[$val]['vi'];
                $metadata["{$field}_en"] = $options[$val]['en'];
            }
        }

        return $metadata;
    }

    private function generateBarcode()
    {
        return $this->barcodeService->getNextCode('item');
    }

    private function generateAccessionNumber()
    {
        return 'ACC' . date('Y') . str_pad(BookItem::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function show(BibliographicRecord $record)
    {
        $record->load('fields.subfields');
        // Fetch definitions to show human-readable labels in the review page
        $definitions = MarcTagDefinition::with('subfields')->get()->keyBy('tag');

        return view('admin.marc_books.show', compact('record', 'definitions'));
    }

    public function updateStatus(Request $request, BibliographicRecord $record)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved',
            'framework' => 'nullable|string',
            'subject_category' => 'nullable|string',
            'record_type' => 'nullable|string',
            'serial_frequency' => 'nullable|string',
            'date_type' => 'nullable|string',
            'acquisition_method' => 'nullable|string',
            'document_format' => 'nullable|string',
            'cataloging_standard' => 'nullable|string',
        ]);

        $record->update($validated);

        return back()->with('success', __('Cập nhật trạng thái thành công'));
    }

    public function update(Request $request, BibliographicRecord $record)
    {
        DB::beginTransaction();
        try {
            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old image if exists
                if ($record->cover_image && \Storage::disk('public')->exists($record->cover_image)) {
                    \Storage::disk('public')->delete($record->cover_image);
                }
                $record->cover_image = $request->file('cover_image')->store('covers', 'public');
            }

            $leader = $record->leader;
            if (isset($leader[5]) && $leader[5] === 'n') {
                $leader[5] = 'c';
            }
            $record->update(['leader' => $leader]);

            // Update record metadata if provided
            $metadata = $this->getMetadataWithNames($request);
            $record->update(array_merge([
                'leader' => $leader,
                'framework' => $request->input('framework', $record->framework),
                'status' => $request->input('status', $record->status),
                'is_featured' => $request->boolean('is_featured'),
                'document_type_id' => $request->input('document_type_id', $record->document_type_id),
                'subject_category' => $request->input('subject_category', $record->subject_category),
            ], $metadata));

            $fields = $request->input('fields', []);
            $sequence = 0;
            
            // Track IDs of fields and subfields that should be KEPT
            $keptFieldIds = [];

            foreach ($fields as $tag => $data) {
                $subfieldEntries = $data['subfields'] ?? [];

                // Kiểm tra xem tag này có dữ liệu hợp lệ không
                $hasValidSubfields = false;
                foreach ($subfieldEntries as $entry) {
                    if (!empty($entry['code']) && (!empty($entry['value']) || $entry['value'] === '0')) {
                        $hasValidSubfields = true;
                        break;
                    }
                }

                // Nếu không có dữ liệu, chúng ta không xóa ở đây mà sẽ để logic Snapshot xử lý (xóa những gì không gửi lên)
                if (!$hasValidSubfields) continue;

                // 1. Cập nhật hoặc Tạo mới MarcField cho Record này
                $marcField = $record->fields()->updateOrCreate(
                    ['tag' => $tag],
                    [
                        'indicator1' => $data['ind1'] ?? ' ',
                        'indicator2' => $data['ind2'] ?? ' ',
                        'sequence' => $sequence++
                    ]
                );
                
                $keptFieldIds[] = $marcField->id;

                $keptSubfieldIds = [];
                // 2. Xử lý các Subfields
                foreach ($subfieldEntries as $entry) {
                    if (!empty($entry['code']) && (!empty($entry['value']) || $entry['value'] === '0')) {
                        $subfield = $marcField->subfields()->updateOrCreate(
                            ['id' => $entry['id'] ?? null],
                            [
                                'code' => substr($entry['code'], 0, 1),
                                'value' => $entry['value']
                            ]
                        );
                        $keptSubfieldIds[] = $subfield->id;
                    }
                }

                // Xóa các subfields của field này mà KHÔNG được gửi lên (bị xóa trên UI)
                $marcField->subfields()->whereNotIn('id', $keptSubfieldIds)->delete();
            }

            // SNAPSHOT SYNC: Xóa tất cả các trường cũ của Record mà KHÔNG có trong danh sách vừa cập nhật
            // Điều này đảm bảo Snapshot luôn khớp với những gì người dùng thấy trên Form
            $record->fields()->whereNotIn('id', $keptFieldIds)->delete();

            // Process distribution items (Add, Update, Delete)
            $items = $request->input('items', []);
            $submittedItemIds = [];

            \Log::info('=== DISTRIBUTION ITEMS DEBUG ===');
            \Log::info('Raw items data:', $items);
            \Log::info('Items count: ' . count($items));

            foreach ($items as $index => $itemData) {
                \Log::info("Processing item {$index}: ", $itemData);
                
                if (!empty($itemData['storage_location_id'])) {
                    \Log::info('Item has storage_location_id, proceeding...');
                    
                    $itemPayload = [
                        'branch_id' => $itemData['branch_id'] ?? null,
                        'storage_location_id' => $itemData['storage_location_id'],
                        'barcode' => $itemData['barcode'] ?? null,
                        'accession_number' => $itemData['accession_number'] ?? $this->generateAccessionNumber(),
                        'storage_type' => $itemData['storage_type'] ?? 'Book',
                        'quantity' => $itemData['quantity'] ?? 1,
                        'status' => $itemData['status'] ?? 'available',
                        'order_code' => $itemData['order_code'] ?? null,
                        'waits_for_print' => isset($itemData['waits_for_print']) ? (bool)$itemData['waits_for_print'] : false,
                        'notes' => $itemData['notes'] ?? null,
                        'volume_issue' => $itemData['volume_issue'] ?? null,
                        'day' => $itemData['day'] ?? null,
                        'month_season' => $itemData['month_season'] ?? null,
                        'year' => $itemData['year'] ?? null,
                        'shelf' => $itemData['shelf'] ?? null,
                        'shelf_position' => $itemData['shelf_position'] ?? null,
                        'location' => $itemData['location'] ?? null,
                        'temporary_location' => $itemData['temporary_location'] ?? null,
                    ];
                    
                    \Log::info('Item payload prepared:', $itemPayload);

                    // Update existing
                    if (!empty($itemData['id'])) {
                        \Log::info('Updating existing item ID: ' . $itemData['id']);
                        $bookItem = BookItem::find($itemData['id']);
                        if ($bookItem && $bookItem->bibliographic_record_id == $record->id) {
                            \Log::info('Found valid book item, updating...');
                            $bookItem->update($itemPayload);
                            $submittedItemIds[] = $bookItem->id;
                            \Log::info('Item updated successfully, ID: ' . $bookItem->id);
                            
                            // Save barcode as SVG file if barcode changed or is new
                            if (!empty($bookItem->barcode)) {
                                \Log::info('Saving barcode SVG for: ' . $bookItem->barcode);
                                $this->barcodeService->saveAsFile(
                                    $bookItem->barcode, 
                                    'items/barcodes/' . $bookItem->barcode . '.svg'
                                );
                            }
                        } else {
                            \Log::warning('Book item not found or does not belong to this record. Item ID: ' . $itemData['id'] . ', Record ID: ' . $record->id);
                        }
                    } else {
                        \Log::info('Creating new item...');
                        // Create new
                        $itemPayload['bibliographic_record_id'] = $record->id;
                        
                        // Handle barcode - either use provided or generate new
                        if (!empty($itemPayload['barcode'])) {
                            // Check if provided barcode already exists
                            if (BookItem::where('barcode', $itemPayload['barcode'])->exists()) {
                                \Log::warning('Barcode already exists, generating new one. Original: ' . $itemPayload['barcode']);
                                $itemPayload['barcode'] = $this->barcodeService->getNextCode('item');
                                $this->barcodeService->incrementCounter('item', $itemPayload['barcode']);
                                \Log::info('Generated new barcode: ' . $itemPayload['barcode']);
                            }
                        } else {
                            \Log::info('Generating new barcode...');
                            $itemPayload['barcode'] = $this->barcodeService->getNextCode('item');
                            $this->barcodeService->incrementCounter('item', $itemPayload['barcode']);
                            \Log::info('Generated barcode: ' . $itemPayload['barcode']);
                        }
                        
                        // Handle accession_number - either use provided or generate new
                        if (!empty($itemPayload['accession_number'])) {
                            // Check if provided accession_number already exists
                            if (BookItem::where('accession_number', $itemPayload['accession_number'])->exists()) {
                                \Log::warning('Accession number already exists, generating new one. Original: ' . $itemPayload['accession_number']);
                                $itemPayload['accession_number'] = $this->generateAccessionNumber();
                                \Log::info('Generated new accession number: ' . $itemPayload['accession_number']);
                            }
                        } else {
                            \Log::info('Using generated accession number: ' . $itemPayload['accession_number']);
                        }

                        \Log::info('Creating BookItem with payload:', $itemPayload);
                        $newItem = BookItem::create($itemPayload);
                        $submittedItemIds[] = $newItem->id;
                        \Log::info('New item created successfully, ID: ' . $newItem->id);
                        
                        // Save barcode as SVG file
                        \Log::info('Saving barcode SVG for new item: ' . $newItem->barcode);
                        $this->barcodeService->saveAsFile(
                            $newItem->barcode, 
                            'items/barcodes/' . $newItem->barcode . '.svg'
                        );
                    }
                } else {
                    \Log::warning('Item skipped - no storage_location_id. Item data:', $itemData);
                }
            }
            
            \Log::info('Submitted item IDs: ', $submittedItemIds);
            \Log::info('=== END DISTRIBUTION ITEMS DEBUG ===');

            // Note: In typical library systems, deleting cataloged items might be restricted 
            // if they are on loan. We assume deletion is allowed here for items removed from UI.
            // Be careful with cascading deletes.
            $record->items()->whereNotIn('id', $submittedItemIds)->delete();

            DB::commit();
            $tab = $request->input('tab', 0);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Cập nhật bản ghi thành công'),
                    'redirect' => route('admin.marc.book.form', ['record' => $record->id, 'tab' => $tab]),
                    'record_id' => $record->id,
                ]);
            }

            return redirect()->route('admin.marc.book.form', ['record' => $record->id, 'tab' => $tab])->with('success', __('Cập nhật bản ghi thành công'));
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('MARC Book Update Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Lỗi khi cập nhật: ') . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', __('Lỗi khi cập nhật: ') . $e->getMessage())->withInput();
        }
    }

    public function destroy(BibliographicRecord $record)
    {
        DB::beginTransaction();
        try {
            // Associated fields, subfields and items will be deleted based on DB constraints or manual deletion
            // Since we use DB::beginTransaction, let's ensure cleanup
            $record->items()->delete();
            foreach ($record->fields as $field) {
                $field->subfields()->delete();
                $field->delete();
            }
            $record->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('Xóa bản ghi thành công')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Lỗi khi xóa: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show export page
     */
    public function exportIndex(Request $request)
    {
        $frameworks = MarcFramework::where('is_active', true)->get();
        $documentTypes = DocumentType::active()->ordered()->get();
        
        return view('admin.marc_books.export', compact('frameworks', 'documentTypes'));
    }

    /**
     * Export MARC records to Excel
     */
    public function export(Request $request)
    {
        $query = BibliographicRecord::with(['items', 'fields.subfields']);
        
        // Apply filters
        if ($request->filled('record_id')) {
            $query->where('id', $request->record_id);
        }
        
        if ($request->filled('framework_id')) {
            $query->where('framework_id', $request->framework_id);
        }
        
        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $records = $query->orderBy('created_at', 'desc')->get();
        
        if ($request->filled('record_id')) {
            $filename = 'marc_record_' . $request->record_id . '_' . now()->format('Y-m-d_H-i-s');
        } else {
            $filename = 'marc_records_export_' . now()->format('Y-m-d_H-i-s');
        }
        
        $includeItems = $request->boolean('include_items', false);
        $format = $request->get('format', 'excel');
        
        switch ($format) {
            case 'csv':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\MarcRecordsExport($records, $includeItems),
                    $filename . '.csv'
                );
                
            case 'marc':
                // Generate MARC format file
                $marcContent = $this->generateMarcFormat($records, $includeItems);
                return response($marcContent)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.txt"');
                
            case 'excel':
            default:
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\MarcRecordsExport($records, $includeItems),
                    $filename . '.xlsx'
                );
        }
    }

    /**
     * Generate MARC format content (ISO 2709 standard)
     */
    private function generateMarcFormat($records, $includeItems = false)
    {
        $marcContent = '';
        
        $FT = chr(0x1E); // Field Terminator
        $US = chr(0x1F); // Unit Separator / Subfield Delimiter
        $RT = chr(0x1D); // Record Terminator
        
        foreach ($records as $record) {
            $fields = $record->fields;
            
            // Generate standard control fields if not present in database
            $has001 = $fields->contains('tag', '001');
            $has005 = $fields->contains('tag', '005');
            $has008 = $fields->contains('tag', '008');
            
            $allFields = collect($fields);
            
            if (!$has001) {
                $allFields->push((object)[
                    'tag' => '001',
                    'indicator1' => '',
                    'indicator2' => '',
                    'subfields' => collect([
                        (object)['code' => 'a', 'value' => str_pad($record->id, 8, '0', STR_PAD_LEFT)]
                    ])
                ]);
            }
            if (!$has005) {
                $allFields->push((object)[
                    'tag' => '005',
                    'indicator1' => '',
                    'indicator2' => '',
                    'subfields' => collect([
                        (object)['code' => 'a', 'value' => ($record->updated_at ?? now())->format('YmdHis.0')]
                    ])
                ]);
            }
            if (!$has008) {
                $lang = 'vie';
                $datePart = ($record->created_at ?? now())->format('ymd');
                $val008 = $datePart . 's' . ($record->publication_year ?? date('Y')) . '    ' . '      ' . '            ' . '||||||' . $lang . ' d';
                $val008 = str_pad(substr($val008, 0, 40), 40, ' ');
                $allFields->push((object)[
                    'tag' => '008',
                    'indicator1' => '',
                    'indicator2' => '',
                    'subfields' => collect([
                        (object)['code' => 'a', 'value' => $val008]
                    ])
                ]);
            }
            
            // Include items if requested
            if ($includeItems && $record->items) {
                foreach ($record->items as $item) {
                    $itemSubfields = collect();
                    if (!empty($item->barcode)) {
                        $itemSubfields->push((object)['code' => 'a', 'value' => $item->barcode]);
                    }
                    if (!empty($item->location)) {
                        $itemSubfields->push((object)['code' => 'b', 'value' => $item->location]);
                    }
                    if (!empty($item->status)) {
                        $itemSubfields->push((object)['code' => 'c', 'value' => $item->status]);
                    }
                    
                    $allFields->push((object)[
                        'tag' => '952',
                        'indicator1' => ' ',
                        'indicator2' => ' ',
                        'subfields' => $itemSubfields
                    ]);
                }
            }
            
            // Sort fields by tag
            $sortedFields = $allFields->sortBy('tag');
            
            $recordData = '';
            $directory = '';
            $dataOffset = 0;
            
            foreach ($sortedFields as $field) {
                $tag = $field->tag;
                $fieldContent = '';
                
                $isControlField = (intval($tag) < 10);
                
                if ($isControlField) {
                    // Control fields: no indicators, no subfield codes, just value + FT
                    $value = $field->subfields->first() ? $field->subfields->first()->value : '';
                    $fieldContent = $value . $FT;
                } else {
                    // Variable data fields: indicator1 + indicator2 + subfields + FT
                    $ind1 = (isset($field->indicator1) && strlen($field->indicator1) === 1) ? $field->indicator1 : ' ';
                    $ind2 = (isset($field->indicator2) && strlen($field->indicator2) === 1) ? $field->indicator2 : ' ';
                    
                    $fieldContent = $ind1 . $ind2;
                    foreach ($field->subfields as $subfield) {
                        $fieldContent .= $US . $subfield->code . $subfield->value;
                    }
                    $fieldContent .= $FT;
                }
                
                $fieldLength = strlen($fieldContent);
                
                // Directory entry: tag (3), length of field (4), starting character position (5)
                $directory .= sprintf('%03s%04d%05d', $tag, $fieldLength, $dataOffset);
                
                $recordData .= $fieldContent;
                $dataOffset += $fieldLength;
            }
            
            // Directory terminator
            $directory .= $FT;
            
            // Base Address and Logical Record Length
            $baseAddress = 24 + strlen($directory);
            $totalLength = $baseAddress + strlen($recordData) + 1; // +1 for RT
            
            // Determine Leader template
            $recordLeader = $record->leader;
            if (strlen($recordLeader) !== 24) {
                $recordLeader = '00000nam a2200000 a 4500';
            }
            
            $status = $recordLeader[5] ?? 'n';
            $type = $recordLeader[6] ?? 'a';
            $bibLevel = $recordLeader[7] ?? 'm';
            $encoding = $recordLeader[17] ?? ' ';
            $descCataloging = $recordLeader[18] ?? 'a';
            $multipart = $recordLeader[19] ?? ' ';
            
            $leader = sprintf('%05d', $totalLength)
                    . $status
                    . $type
                    . $bibLevel
                    . '  22'
                    . sprintf('%05d', $baseAddress)
                    . $encoding
                    . $descCataloging
                    . $multipart
                    . '4500';
            
            $marcContent .= $leader . $directory . $recordData . $RT . "\n";
        }
        
        return $marcContent;
    }
}
