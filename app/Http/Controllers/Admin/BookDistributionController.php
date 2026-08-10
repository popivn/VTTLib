<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BibliographicRecord;
use App\Models\BookItem;
use Illuminate\Http\Request;

class BookDistributionController extends Controller
{
    protected $barcodeService;

    public function __construct(\App\Services\BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    public function index(BibliographicRecord $record)
    {
        $record->load('items.branch', 'items.storageLocation');
        
        if (!$this->barcodeService->hasActiveRule('item')) {
            session()->flash('warning', __('Hệ thống chưa thiết lập quy tắc mã vạch cho Tài liệu'));
        }

        $nextBarcode = $this->barcodeService->previewNextBarcode('item');
        $branches = \App\Models\Branch::with('storageLocations')->where('is_active', true)->get();
        return view('admin.distributions.index', compact('record', 'nextBarcode', 'branches'));
    }

    public function checkBarcode(Request $request)
    {
        $barcode = $request->query('barcode');
        $type = $request->query('type', 'barcode');

        if ($type === 'accession_number') {
            $exists = BookItem::where('accession_number', $barcode)->exists();
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? __('Số đăng ký cá biệt đã tồn tại') : __('Số đăng ký cá biệt hợp lệ')
            ]);
        }

        $exists = BookItem::where('barcode', $barcode)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? __('Mã vạch đã tồn tại') : __('Mã vạch hợp lệ')
        ]);
    }

    public function store(Request $request, BibliographicRecord $record)
    {
        $validated = $request->validate([
            'barcode' => 'nullable|string',
            'accession_number' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'storage_location_id' => 'required|exists:storage_locations,id',
            'storage_type' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'location' => 'nullable|string',
            'temporary_location' => 'nullable|string',
            'status' => 'required|string',
            'order_code' => 'nullable|string',
            'waits_for_print' => 'boolean',
            'notes' => 'nullable|string',
            'volume_issue' => 'nullable|string',
            'day' => 'nullable|integer',
            'month_season' => 'nullable|string',
            'year' => 'nullable|integer',
            'shelf' => 'nullable|string',
            'shelf_position' => 'nullable|string',
        ]);

        $quantity = (int) $validated['quantity'];
        $baseBarcode = $validated['barcode'] ?? '';
        $baseAccession = $validated['accession_number'];

        // Parse barcode to extract prefix and numeric suffix for incrementing
        // e.g. "2026-0312" => prefix="2026-0", numericPart="312", padLength=3
        $barcodePrefix = '';
        $barcodeNumeric = null;
        $barcodePadLength = 0;
        if (!empty($baseBarcode)) {
            if (preg_match('/^(.*?)(\d+)$/', $baseBarcode, $m)) {
                $barcodePrefix = $m[1];
                $barcodeNumeric = (int) $m[2];
                $barcodePadLength = strlen($m[2]);
            } else {
                $barcodePrefix = $baseBarcode;
                $barcodeNumeric = null;
            }
        }

        // Parse accession_number similarly
        $accPrefix = '';
        $accNumeric = null;
        $accPadLength = 0;
        if (!empty($baseAccession)) {
            if (preg_match('/^(.*?)(\d+)$/', $baseAccession, $m)) {
                $accPrefix = $m[1];
                $accNumeric = (int) $m[2];
                $accPadLength = strlen($m[2]);
            } else {
                $accPrefix = $baseAccession;
                $accNumeric = null;
            }
        }

        $createdBarcodes = [];
        $createdItems = [];

        for ($i = 0; $i < $quantity; $i++) {
            // Generate barcode for this item
            if (!empty($baseBarcode)) {
                if ($barcodeNumeric !== null) {
                    $currentBarcode = $barcodePrefix . str_pad($barcodeNumeric + $i, $barcodePadLength, '0', STR_PAD_LEFT);
                } else {
                    $currentBarcode = $baseBarcode;
                }
            } else {
                try {
                    $currentBarcode = $this->barcodeService->getNextBarcode('item');
                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                }
            }

            // Generate accession number for this item
            if ($accNumeric !== null) {
                $currentAccession = $accPrefix . str_pad($accNumeric + $i, $accPadLength, '0', STR_PAD_LEFT);
            } else {
                $currentAccession = $baseAccession;
            }

            // Check duplicate barcode
            if (BookItem::where('barcode', $currentBarcode)->exists()) {
                return back()->withInput()->with('error',
                    __('Mã vạch :barcode đã tồn tại trong hệ thống. Vui lòng dùng mã vạch khác.', ['barcode' => $currentBarcode]));
            }

            // Check duplicate accession_number
            if (BookItem::where('accession_number', $currentAccession)->exists()) {
                return back()->withInput()->with('error',
                    __('Số đăng ký cá biệt :acc đã tồn tại trong hệ thống.', ['acc' => $currentAccession]));
            }

            $itemData = $validated;
            $itemData['barcode'] = $currentBarcode;
            $itemData['accession_number'] = $currentAccession;
            unset($itemData['quantity']);

            // Generate barcode image (SVG)
            try {
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                $barcodeData = $generator->getBarcode($currentBarcode, $generator::TYPE_CODE_128);

                $path = public_path('barcode/' . $currentBarcode . '.svg');

                if (!file_exists(public_path('barcode'))) {
                    mkdir(public_path('barcode'), 0755, true);
                }

                file_put_contents($path, $barcodeData);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Barcode generation failed: ' . $e->getMessage());
            }

            $createdItems[] = $record->items()->create($itemData);
            $createdBarcodes[] = $currentBarcode;
        }

        $msg = __('Đã phân bổ :count bản sách và tạo mã vạch thành công.', ['count' => $quantity]);
        if ($quantity > 1) {
            $msg .= ' ' . __('Mã vạch: :barcodes', ['barcodes' => implode(', ', $createdBarcodes)]);
        }

        return back()->with('success', $msg);
    }
}
