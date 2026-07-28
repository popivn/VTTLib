<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Display a listing of videos.
     */
    public function index(Request $request)
    {
        $query = Video::with('creator');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $videos = $query->ordered()->paginate(20);

        return view('admin.videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new video.
     */
    public function create()
    {
        return view('admin.videos.create');
    }

    /**
     * Store a newly created video in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'video_path' => 'required|string', // Path từ AJAX upload
            'video_size' => 'required|integer',
            'video_mime_type' => 'required|string',
            'thumbnail_path' => 'nullable|string', // Path từ AJAX upload
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0'
        ], [
            'video_path.required' => 'Vui lòng chọn và tải lên video trước khi lưu!',
            'video_size.required' => 'Thông tin kích thước video bị thiếu.',
            'video_mime_type.required' => 'Thông tin loại file video bị thiếu.',
            'title.required' => 'Tên video không được để trống.',
        ]);

        try {
            // Create video record với thông tin đã upload
            $video = Video::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'path' => $validated['video_path'],
                'thumbnail' => $validated['thumbnail_path'],
                'file_size' => $validated['video_size'],
                'mime_type' => $validated['video_mime_type'],
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.videos.index')
                ->with('success', 'Video đã được tạo thành công!');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Lỗi khi tạo video: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified video.
     */
    public function show(Video $video)
    {
        return view('admin.videos.show', compact('video'));
    }

    /**
     * Show the form for editing the specified video.
     */
    public function edit(Video $video)
    {
        return view('admin.videos.edit', compact('video'));
    }

    /**
     * Update the specified video in storage.
     */
    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,webm,mkv|max:512000',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
            ];

            // Update video file if provided
            if ($request->hasFile('video_file')) {
                // Delete old video
                if ($video->path && Storage::disk('public')->exists($video->path)) {
                    Storage::disk('public')->delete($video->path);
                }
                
                // Store new video
                $videoPath = $request->file('video_file')->store('videos', 'public');
                $file = $request->file('video_file');
                
                $updateData['path'] = $videoPath;
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
            }

            // Update thumbnail if provided
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
                
                $updateData['thumbnail'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
            }

            $video->update($updateData);

            return redirect()->route('admin.videos.index')
                ->with('success', 'Video đã được cập nhật thành công!');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Lỗi khi cập nhật video: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified video from storage.
     */
    public function destroy(Video $video)
    {
        try {
            // Delete video file
            if ($video->path && Storage::disk('public')->exists($video->path)) {
                Storage::disk('public')->delete($video->path);
            }

            // Delete thumbnail
            if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            return redirect()->route('admin.videos.index')
                ->with('success', 'Video đã được xóa thành công!');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa video: ' . $e->getMessage());
        }
    }

    /**
     * Upload video file via AJAX with progress
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video_file' => 'required|file|mimes:mp4,avi,mov,wmv,flv,webm,mkv|max:512000',
        ]);

        try {
            // Store video file
            $videoPath = $request->file('video_file')->store('videos', 'public');
            
            // Get file info
            $file = $request->file('video_file');
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            
            return response()->json([
                'success' => true,
                'path' => $videoPath,
                'size' => $fileSize,
                'mime_type' => $mimeType,
                'formatted_size' => $this->formatFileSize($fileSize),
                'file_name' => $file->getClientOriginalName()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload thumbnail via AJAX
     */
    public function uploadThumbnail(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        try {
            // Store thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('videos/thumbnails', 'public');
            
            return response()->json([
                'success' => true,
                'path' => $thumbnailPath,
                'url' => asset('storage/' . $thumbnailPath)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thumbnail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format file size helper
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    public function toggleStatus(Video $video)
    {
        try {
            $video->update(['is_active' => !$video->is_active]);
            
            $status = $video->is_active ? 'kích hoạt' : 'vô hiệu hóa';
            
            return response()->json([
                'success' => true,
                'message' => "Video đã được {$status}!",
                'is_active' => $video->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái!'
            ], 500);
        }
    }
}