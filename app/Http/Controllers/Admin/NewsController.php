<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsController extends Controller
{
    /**
     * Display a listing of news.
     */
    public function index(Request $request)
    {
        $query = News::with(['category', 'author']);

        $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('summary', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $news = $query->paginate(15);
        
        // Get filter options
        $categories = NewsCategory::active()->orderBy('name')->get();
        $authors = DB::table('users')->select('id', 'name')->get();
        
        // Statistics
        $stats = [
            'total' => News::count(),
            'published' => News::published()->count(),
            'draft' => News::draft()->count(),
            'pending' => News::pending()->count(),
            'featured' => News::featured()->count(),
        ];

        return view('admin.news.index', compact('news', 'categories', 'authors', 'stats'));
    }

    /**
     * Show the form for creating a new news.
     */
    public function create()
    {
        $categories = NewsCategory::active()->orderBy('name')->get();
        $tags = NewsTag::active()->orderBy('name')->get();
        
        return view('admin.news.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created news.
     */
    public function store(Request $request)
    {
        // Debugging data before validation
        if ($request->has('debug')) {
            dd($request->all());
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'featured_image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480',
            'category_id' => 'nullable|exists:news_categories,id',
            'status' => 'required|in:draft,pending,published,archived',
            'published_at' => 'nullable|date',
            'expired_at' => 'nullable|date|after:published_at',
            'is_featured' => 'nullable',
            'allow_comments' => 'nullable',
            'language' => 'required|string|max:5',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
        ]);

        // Process tags input string into an array of names
        $tagNames = [];
        if (!empty($request->input('tags'))) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->input('tags'))));
        }
        unset($validated['tags']);

        // Handle image upload or URL
        if ($request->hasFile('featured_image_file')) {
            $path = $request->file('featured_image_file')->store('news', 'public');
            $validated['featured_image'] = asset('storage/' . $path);
        } elseif ($request->input('image_removed') == '1') {
            $validated['featured_image'] = null;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Check if slug already exists and make it unique if it does
        $originalSlug = $validated['slug'];
        $count = 1;
        while (News::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count++;
        }

        // Set default values
        $validated['author_id'] = auth()->id();
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Handle dates
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        DB::beginTransaction();
        try {
            $news = News::create($validated);

            // Sync tags
            if (!empty($tagNames)) {
                $news->syncTags($tagNames);
            }

            // Log activity
            activity_log('news_created', $news, [
                'title' => $news->title,
                'status' => $news->status
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news.index')
                ->with('success', 'Tạo tin tức thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified news.
     */
    public function show(News $news)
    {
        $news->load(['category', 'author', 'tags']);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified news.
     */
    public function edit(News $news)
    {
        $categories = NewsCategory::active()->get();
        $news->load(['tags']);
        return view('admin.news.edit', compact('news', 'categories'));
    }

    /**
     * Update the specified news.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news,slug,' . $news->id,
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:255',
            'featured_image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480',
            'category_id' => 'nullable|exists:news_categories,id',
            'status' => 'required|in:draft,pending,published,archived',
            'published_at' => 'nullable|date',
            'expired_at' => 'nullable|date|after:published_at',
            'is_featured' => 'nullable',
            'allow_comments' => 'nullable',
            'language' => 'required|string|max:5',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
        ]);

        // Process tags input string into an array of names
        $tagNames = [];
        if (!empty($request->input('tags'))) {
            $tagNames = array_filter(array_map('trim', explode(',', $request->input('tags'))));
        }
        unset($validated['tags']);

        // Handle image upload or URL
        if ($request->hasFile('featured_image_file')) {
            $path = $request->file('featured_image_file')->store('news', 'public');
            $validated['featured_image'] = asset('storage/' . $path);
        } elseif ($request->input('image_removed') == '1') {
            $validated['featured_image'] = null;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set default values
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Handle dates
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        DB::beginTransaction();
        try {
            $news->update($validated);

            // Sync tags
            if (!empty($tagNames)) {
                $news->syncTags($tagNames);
            } else {
                $news->tags()->detach();
            }           

            // Log activity
            activity_log('news_updated', $news, [
                'title' => $news->title,
                'status' => $news->status
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news.index')
                ->with('success', 'Cập nhật tin tức thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật tin tức: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified news.
     */
    public function destroy(News $news)
    {
        DB::beginTransaction();
        try {
            $title = $news->title;
            
            $news->delete();

            // Log activity
            activity_log('news_deleted', null, [
                'title' => $title
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news.index')
                ->with('success', 'Xóa tin tức thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi xóa tin tức: ' . $e->getMessage());
        }
    }

    /**
     * Publish news (AJAX)
     */
    public function publish(News $news)
    {
        try {
            $news->publish();
            
            return response()->json([
                'success' => true,
                'message' => 'Tin tức đã được đăng!',
                'status' => $news->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng tin tức!'
            ], 500);
        }
    }

    /**
     * Archive news (AJAX)
     */
    public function archive(News $news)
    {
        try {
            $news->archive();
            
            return response()->json([
                'success' => true,
                'message' => 'Tin tức đã được lưu trữ!',
                'status' => $news->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu trữ tin tức!'
            ], 500);
        }
    }

    /**
     * Toggle featured status (AJAX)
     */
    public function toggleFeatured(News $news)
    {
        try {
            $news->is_featured = !$news->is_featured;
            $news->save();
            
            $status = $news->is_featured ? 'nổi bật' : 'thường';
            
            return response()->json([
                'success' => true,
                'message' => "Tin tức đã được đặt làm {$status}!",
                'is_featured' => $news->is_featured
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái!'
            ], 500);
        }
    }

    /**
     * Bulk actions (AJAX)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,archive,delete,feature,unfeature',
            'news_ids' => 'required|array',
            'news_ids.*' => 'exists:news,id'
        ]);

        DB::beginTransaction();
        try {
            $newsIds = $validated['news_ids'];
            $count = 0;

            switch ($validated['action']) {
                case 'publish':
                    News::whereIn('id', $newsIds)->update([
                        'status' => 'published',
                        'published_at' => now()
                    ]);
                    $count = count($newsIds);
                    $message = "Đã đăng {$count} tin tức";
                    break;

                case 'archive':
                    News::whereIn('id', $newsIds)->update(['status' => 'archived']);
                    $count = count($newsIds);
                    $message = "Đã lưu trữ {$count} tin tức";
                    break;

                case 'delete':
                    News::whereIn('id', $newsIds)->delete();
                    $count = count($newsIds);
                    $message = "Đã xóa {$count} tin tức";
                    break;

                case 'feature':
                    News::whereIn('id', $newsIds)->update(['is_featured' => true]);
                    $count = count($newsIds);
                    $message = "Đưa {$count} tin tức lên nổi bật";
                    break;

                case 'unfeature':
                    News::whereIn('id', $newsIds)->update(['is_featured' => false]);
                    $count = count($newsIds);
                    $message = "Bỏ {$count} tin tức khỏi nổi bật";
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics (AJAX)
     */
    public function statistics()
    {
        $stats = [
            'total' => News::count(),
            'published' => News::published()->count(),
            'draft' => News::draft()->count(),
            'pending' => News::pending()->count(),
            'archived' => News::archived()->count(),
            'featured' => News::featured()->count(),
            'this_month' => News::whereMonth('created_at', now()->month)->count(),
            'last_month' => News::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Category statistics
        $categoryStats = NewsCategory::withCount('publishedNews')
            ->active()
            ->orderBy('published_news_count', 'desc')
            ->limit(5)
            ->get();

        // Popular news
        $popularNews = News::published()
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'view_count']);

        return response()->json([
            'stats' => $stats,
            'categoryStats' => $categoryStats,
            'popularNews' => $popularNews
        ]);
    }

    /**
     * Auto generate news content.
     */
    public function autoGenerate(Request $request)
    {
        try {
            // Get category if provided
            $categoryId = $request->input('category_id');
            
            if (!$categoryId) {
                $categories = NewsCategory::active()->get();
                $categoryId = $categories->isNotEmpty() ? $categories->random()->id : null;
            }
            
            // Get the current max sort_order
            $maxOrder = News::max('sort_order') ?? -1;

            $topics = [
                'Thông báo về việc mượn trả sách trong kỳ nghỉ lễ',
                'Giới thiệu bộ sưu tập giáo trình y khoa mới nhất 2026',
                'Hướng dẫn sử dụng hệ thống thư viện số VTTLib',
                'Danh sách các ebook mới được cập nhật trong tháng',
                'Tin tức về hoạt động nghiên cứu khoa học của sinh viên',
                'Hội thảo trực tuyến: Khai thác tài nguyên số hiệu quả',
                'Cập nhật quy định về bảo quản tài liệu điện tử',
                'Top 10 cuốn sách được mượn nhiều nhất tháng qua',
                'Phỏng vấn bạn đọc tiêu biểu của thư viện số',
                'VTTU mở rộng hợp tác quốc tế trong chia sẻ học liệu'
            ];

            $topic = $topics[array_rand($topics)];
            $content = "Đây là nội dung tự động được tạo cho bài viết: {$topic}.\n\n" .
                      "Thư viện số VTTLib không ngừng cải tiến và cập nhật những tài liệu quý giá nhất phục vụ cộng đồng giảng viên và sinh viên. " .
                      "Bài viết này cung cấp cái nhìn chi tiết về các hoạt động và tài nguyên mới nhất mà chúng tôi vừa triển khai.\n\n" .
                      "Các điểm chính bao gồm:\n" .
                      "1. Giới thiệu tổng quan về sự kiện/tài liệu.\n" .
                      "2. Lợi ích mang lại cho người dùng.\n" .
                      "3. Hướng dẫn cách thức tiếp cận và sử dụng.\n\n" .
                      "Mọi ý kiến đóng góp xin vui lòng liên hệ bộ phận hỗ trợ kỹ thuật của thư viện.";

            $news = News::create([
                'title' => $topic . ' - ' . now()->format('H:i:s'),
                'slug' => Str::slug($topic) . '-' . time(),
                'summary' => 'Tóm tắt tự động cho bài viết: ' . $topic,
                'content' => $content,
                'status' => 'published',
                'category_id' => $categoryId,
                'author_id' => auth()->id(),
                'published_at' => now(),
                'language' => 'vi',
                'is_featured' => rand(0, 1),
                'sort_order' => $maxOrder + 1,
                'allow_comments' => true,
                'featured_image' => 'https://img.freepik.com/free-vector/digital-library-concept-illustration_114360-8451.jpg'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tự động tạo thành công!',
                'news' => $news,
                'redirect' => $request->has('category_id') ? route('admin.news.announcements') : route('admin.news.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tự động tạo tin tức: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder news.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:news,id'
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->ids as $index => $id) {
                News::where('id', $id)->update(['sort_order' => $index]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thứ tự thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thứ tự: ' . $e->getMessage()
            ], 500);
        }
    }
}
