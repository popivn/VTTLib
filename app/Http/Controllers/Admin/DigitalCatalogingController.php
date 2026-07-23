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
        $id = $request->input('id');
        $isEdit = !empty($id);

        $rules = [
            'folder_id' => 'required|exists:digital_folders,id',
            'title' => 'required|string|max:255',
            'language' => 'required|string',
            'pages' => 'nullable|integer|min:1',
            'file_resource' => ($isEdit ? 'nullable' : 'required') . '|file|mimes:pdf|max:51200',
            'cover_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480',
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
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('digital_resources', $fileName, 'public');
            $resource->file_path = $filePath;
            $resource->file_name = $file->getClientOriginalName();
            $resource->file_size = $file->getSize();
            $resource->format = $file->getClientOriginalExtension();
        }

        // Xử lý upload ảnh bìa
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = 'cover_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('covers', $imageName, 'public');
            $resource->cover_path = $imagePath;
        }

        $resource->save();

        return redirect()->route('admin.digital-cataloging.index', ['category_id' => $request->folder_id])
                         ->with('success', $isEdit ? 'Cập nhật tài liệu số thành công!' : 'Biên mục tài liệu số thành công!');
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
}
