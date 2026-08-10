<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalCategory;
use App\Models\DigitalResource;
use Illuminate\Http\Request;

class DigitalCatalogingController extends Controller
{
    public function index(Request $request)
    {
        // Lấy thư mục gốc (parent_id = null) kèm thư mục con và số lượng tài liệu
        $categories = \App\Models\DigitalFolder::withCount('resources')
            ->with(['children' => function ($q) {
                $q->withCount('resources')->orderBy('folder_name');
            }])
            ->whereNull('parent_id')
            ->orderBy('folder_name')
            ->get();

        // Lấy danh sách tài liệu
        $query = DigitalResource::with(['folder', 'creator']);
        
        if ($request->has('category_id')) {
            $query->where('folder_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('identifier', 'like', '%' . $request->search . '%');
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.digital_cataloging.index', compact('categories', 'resources'));
    }

    public function create(Request $request)
    {
        $folderId = $request->category_id;
        $folder = \App\Models\DigitalFolder::findOrFail($folderId);
        $resource = new DigitalResource(); // Khởi tạo object rỗng cho form dùng chung
        
        return view('admin.digital_cataloging.create', compact('folder', 'resource'));
    }

    public function edit($id)
    {
        $resource = DigitalResource::findOrFail($id);
        $folder = $resource->folder;
        
        return view('admin.digital_cataloging.create', compact('folder', 'resource'));
    }

    public function store(Request $request)
    {
        // DEBUG: Log toàn bộ thông tin request để chẩn đoán trên server
        logger()->info('DigitalCataloging store() DEBUG', [
            'isEdit' => !empty($request->input('id')),
            'id' => $request->input('id'),
            'php_limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ],
            'request_method' => $request->method(),
            'content_length' => $request->header('Content-Length'),
            'has_file_resource' => $request->hasFile('file_resource'),
            'has_file_resource_key' => $request->files->has('file_resource'),
            'all_input_keys' => array_keys($request->all()),
            'files_keys' => array_keys($_FILES),
            '_files' => $_FILES,
            '_post' => $request->post(),
        ]);

        // Phát hiện post_max_size bị vượt: PHP silently drop toàn bộ POST,
        // $_POST và $_FILES đều rỗng nhưng Content-Length header vẫn có.
        $contentLength = (int) ($request->header('Content-Length') ?? 0);
        $postMaxSize = $this->returnBytes(ini_get('post_max_size'));
        if ($contentLength > 0 && $contentLength > $postMaxSize && count($request->post()) === 0) {
            logger()->error('DigitalCataloging: post_max_size bị vượt', [
                'content_length' => $contentLength,
                'post_max_size' => ini_get('post_max_size'),
                'post_max_size_bytes' => $postMaxSize,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ]);
            return back()->withInput()->with('error',
                'Dữ liệu gửi lên (' . number_format($contentLength / 1024 / 1024, 1) . 'MB) vượt quá giới hạn post_max_size ('
                . ini_get('post_max_size') . '). Vui lòng tăng post_max_size và upload_max_filesize trong php.ini trên server.');
        }

        $id = $request->input('id');
        $isEdit = !empty($id);

        // Chỉ áp dụng rule file khi thực sự có file hợp lệ được upload.
        // Tránh lỗi validation.uploaded khi input file rỗng (UPLOAD_ERR_NO_FILE).
        $hasFileResource = $request->hasFile('file_resource');
        $hasCoverImage = $request->hasFile('cover_image');

        // DEBUG: Log chi tiết file resource nếu có
        if ($hasFileResource) {
            $f = $request->file('file_resource');
            logger()->info('DigitalCataloging file_resource DEBUG', [
                'isValid' => $f->isValid(),
                'getError' => $f->getError(),
                'getErrorMessage' => $f->getErrorMessage(),
                'clientOriginalName' => $f->getClientOriginalName(),
                'clientOriginalExtension' => $f->getClientOriginalExtension(),
                'clientMimeType' => $f->getClientMimeType(),
                'guessExtension' => $f->guessExtension(),
                'getMimeType' => $f->getMimeType(),
                'getSize' => $f->getSize(),
                'path' => $f->getPathname(),
                'realPath' => $f->getRealPath(),
            ]);
        } else {
            logger()->warning('DigitalCataloging: hasFile(file_resource) = FALSE', [
                'files_keys' => array_keys($_FILES),
                'is_edit' => $isEdit,
            ]);
        }

        $rules = [
            'folder_id' => 'required|exists:digital_folders,id',
            'title' => 'required|string|max:255',
            'language' => 'required|string',
            'pages' => 'nullable|integer|min:1',
            'file_resource' => $isEdit
                ? ($hasFileResource ? 'file|mimes:pdf' : 'nullable')
                : 'required|file|mimes:pdf',
            'cover_image' => $hasCoverImage
                ? 'file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480'
                : 'nullable',
        ];

        $request->validate($rules);

        if ($isEdit) {
            $resource = DigitalResource::findOrFail($id);
        } else {
            $resource = new DigitalResource();
            $resource->created_by = auth()->id();
        }

        $resource->folder_id = $request->folder_id;
        $resource->title = $request->title;
        
        // Tách chuỗi tác giả thành mảng
        if ($request->authors) {
            $authorsArray = array_map('trim', explode(',', $request->authors));
            $resource->authors = array_filter($authorsArray);
        } else {
            $resource->authors = [];
        }

        $resource->resource_type = $request->resource_type;
        $resource->language = $request->language;
        $resource->description = $request->description;
        $resource->publisher = $request->publisher;
        
        // Tương tự cho tác giả phụ
        if ($request->secondary_authors) {
            $sAuthorsArray = array_map('trim', explode(',', $request->secondary_authors));
            $resource->secondary_authors = array_filter($sAuthorsArray);
        } else {
            $resource->secondary_authors = [];
        }

        $resource->publish_year = $request->publish_year;
        $resource->format = $request->format;
        $resource->identifier = $request->identifier;
        $resource->pages = $request->pages;
        $resource->source = $request->source;
        $resource->link = $request->link;
        $resource->coverage = $request->coverage;
        $resource->copyright = $request->copyright;
        $resource->cataloging_link = $request->cataloging_link;
        $resource->status = $request->input('status', 'published');

        // Xử lý upload file PDF
        if ($request->hasFile('file_resource')) {
            $file = $request->file('file_resource');

            if (!$file->isValid()) {
                logger()->error('DigitalCataloging: File upload không hợp lệ', [
                    'id' => $resource->id,
                    'originalName' => $file->getClientOriginalName(),
                    'error' => $file->getError(),
                    'errorMessage' => $file->getErrorMessage(),
                ]);
                return back()->withInput()->with('error', 'Tệp PDF tải lên thất bại: ' . $file->getErrorMessage());
            }

            $fileName = time() . '_' . $file->getClientOriginalName();

            // DEBUG: Log trước khi storeAs
            $storageRoot = storage_path('app/public/digital_resources');
            logger()->info('DigitalCataloging: Trước storeAs()', [
                'fileName' => $fileName,
                'storage_root' => $storageRoot,
                'storage_root_exists' => is_dir($storageRoot),
                'storage_root_writable' => is_writable($storageRoot),
                'storage_app_public_writable' => is_writable(storage_path('app/public')),
                'tmp_file_exists' => file_exists($file->getRealPath()),
                'tmp_file_readable' => is_readable($file->getRealPath()),
            ]);

            $filePath = $file->storeAs('digital_resources', $fileName, 'public');

            // DEBUG: Log sau storeAs
            logger()->info('DigitalCataloging: Sau storeAs()', [
                'filePath_result' => $filePath,
                'filePath_type' => gettype($filePath),
                'filePath_is_false' => $filePath === false,
            ]);

            if ($filePath === false) {
                logger()->error('DigitalCataloging: storeAs thất bại cho file_resource', [
                    'id' => $resource->id,
                    'fileName' => $fileName,
                    'disk' => 'public',
                    'root' => storage_path('app/public'),
                ]);
                return back()->withInput()->with('error', 'Không thể lưu tệp PDF. Vui lòng kiểm tra quyền ghi thư mục storage/app/public/digital_resources.');
            }

            // Xóa file cũ nếu đang edit
            if ($isEdit && $resource->getOriginal('file_path')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($resource->getOriginal('file_path'));
            }

            $resource->file_path = $filePath;
            $resource->file_name = $file->getClientOriginalName();
            $resource->file_size = $file->getSize();
            $resource->format = $file->getClientOriginalExtension();
        }

        // Xử lý upload ảnh bìa
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');

            if ($image->isValid()) {
                $imageName = 'cover_' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('covers', $imageName, 'public');

                if ($imagePath !== false) {
                    if ($isEdit && $resource->getOriginal('cover_path')) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($resource->getOriginal('cover_path'));
                    }
                    $resource->cover_path = $imagePath;
                } else {
                    logger()->error('DigitalCataloging: storeAs thất bại cho cover_image', [
                        'id' => $resource->id,
                    ]);
                }
            }
        }

        $resource->save();

        if ($isEdit) {
            return redirect()->route('admin.digital-cataloging.edit', $resource->id)
                             ->with('success', 'Cập nhật tài liệu số thành công!');
        }

        return redirect()->route('admin.digital-cataloging.index', ['category_id' => $request->folder_id])
                         ->with('success', 'Biên mục tài liệu số thành công!');
    }

    public function destroy($id)
    {
        $resource = DigitalResource::findOrFail($id);
        
        // Có thể thêm xóa file vật lý ở đây nếu muốn
        // Storage::disk('public')->delete($resource->file_path);
        // if ($resource->cover_path) Storage::disk('public')->delete($resource->cover_path);

        $resource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tài liệu đã được xóa thành công!'
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'folder_code' => 'required|string|unique:digital_folders,folder_code|max:50',
            'folder_name' => 'required|string|max:255',
        ]);

        $folder = new \App\Models\DigitalFolder();
        $folder->folder_code = $request->folder_code;
        $folder->folder_name = $request->folder_name;
        $folder->is_active = true;
        $folder->save();

        return redirect()->route('admin.digital-cataloging.index')
                         ->with('success', 'Đã thêm phân mục mới thành công!');
    }

    /**
     * Chuyển giá trị ini (như "40M", "512K") sang bytes.
     */
    private function returnBytes($val)
    {
        if (empty($val)) return 0;
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $bytes = (int) $val;
        switch ($last) {
            case 'g': $bytes *= 1024;
            case 'm': $bytes *= 1024;
            case 'k': $bytes *= 1024;
        }
        return $bytes;
    }
}
