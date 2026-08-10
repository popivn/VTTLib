<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalFolder;
use App\Models\DigitalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalResourceController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_folders' => DigitalFolder::count(),
            'total_resources' => DigitalResource::count(),
            'total_views' => DigitalResource::sum('view_count'),
            'total_downloads' => DigitalResource::sum('download_count'),
            'storage_used' => DigitalResource::sum('file_size'),
            'recent_resources' => DigitalResource::with('folder')->latest()->take(5)->get(),
            'top_viewed' => DigitalResource::orderBy('view_count', 'desc')->take(5)->get(),
        ];

        return view('admin.digital-resources.dashboard', compact('stats'));
    }

    public function index(Request $request)
    {
        $folderId = $request->query('folder_id');
        $folder = DigitalFolder::findOrFail($folderId);
        $resources = DigitalResource::where('folder_id', $folderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.digital-resources.index', compact('resources', 'folder'));
    }

    public function create(Request $request)
    {
        $folderId = $request->query('folder_id');
        $folder = DigitalFolder::findOrFail($folderId);
        return view('admin.digital-resources.create', compact('folder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:digital_folders,id',
            'title' => 'required|string|max:500',
            'resource_type' => 'required|string',
            'language' => 'required|string',
            'file' => 'required|file|max:51200', // Tối đa 50MB
            // Metadata fields (+)
            'authors' => 'nullable|array',
            'subjects' => 'nullable|array',
            'topics' => 'nullable|array',
            'secondary_authors' => 'nullable|array',
        ]);

        $file = $request->file('file');
        $path = $file->store('digital_resources/' . date('Y/m'), 'public');

        $resource = new DigitalResource($request->except('file', 'authors', 'subjects', 'topics', 'secondary_authors'));
        $resource->file_path = $path;
        $resource->file_name = $file->getClientOriginalName();
        $resource->file_size = $file->getSize();
        $resource->format = $file->getClientOriginalExtension();
        
        // Lưu các trường JSON (+)
        $resource->authors = $request->input('authors', []);
        $resource->subjects = $request->input('subjects', []);
        $resource->topics = $request->input('topics', []);
        $resource->secondary_authors = $request->input('secondary_authors', []);
        
        $resource->created_by = auth()->id();
        $resource->status = $request->has('publish') ? 'published' : 'draft';
        $resource->save();

        return redirect()->route('admin.digital-resources.index', ['folder_id' => $resource->folder_id])
            ->with('success', __('Tài liệu đã được lưu thành công!'));
    }

    public function show(DigitalResource $resource)
    {
        $resource->increment('view_count');
        return view('admin.digital-resources.show', compact('resource'));
    }

    public function download(DigitalResource $resource)
    {
        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            return back()->with('error', __('Tệp tài liệu không tồn tại trên hệ thống. Vui lòng tải lại tệp PDF.'));
        }

        $resource->increment('download_count');
        return Storage::disk('public')->download($resource->file_path, $resource->file_name);
    }

    public function toggleStatus(DigitalResource $resource)
    {
        $resource->status = $resource->status === 'published' ? 'draft' : 'published';
        $resource->save();
        return back()->with('success', __('Đã cập nhật trạng thái tài liệu!'));
    }

    public function destroy(DigitalResource $resource)
    {
        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();
        return back()->with('success', __('Đã xóa tài liệu thành công!'));
    }
}
