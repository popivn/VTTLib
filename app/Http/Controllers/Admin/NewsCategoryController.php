<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    /**
     * Display a listing of news categories.
     */
    public function index(Request $request)
    {
        $query = NewsCategory::with(['parent', 'children', 'news' => function ($q) {
            $q->select('id', 'category_id', 'status');
        }])->orderBy('sort_order')->orderBy('name');

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->get();
        
        // Build tree structure
        $tree = $this->buildTree($categories->toArray());

        return view('admin.news-categories.index', compact('tree'));
    }

    /**
     * Show the form for creating a new news category.
     */
    public function create()
    {
        $parents = NewsCategory::whereNull('parent_id')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.news-categories.create', compact('parents'));
    }

    /**
     * Store a newly created news category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:news_categories,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:news_categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'language' => 'required|string|max:5',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string'
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default values
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        DB::beginTransaction();
        try {
            $category = NewsCategory::create($validated);

            // Log activity
            activity_log('news_category_created', $category, [
                'name' => $category->name
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'Tạo chuyên mục thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Lỗi khi tạo chuyên mục: ' . $e->getMessage());
        }
    }

    /**
     * Show the specified news category.
     */
    public function show(NewsCategory $newsCategory)
    {
        $newsCategory->load(['parent', 'children', 'news' => function ($q) {
            $q->with('author')->orderBy('created_at', 'desc');
        }]);

        return view('admin.news-categories.show', compact('newsCategory'));
    }

    /**
     * Show the form for editing the specified news category.
     */
    public function edit(NewsCategory $newsCategory)
    {
        $parents = NewsCategory::whereNull('parent_id')
            ->where('id', '!=', $newsCategory->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.news-categories.edit', compact('newsCategory', 'parents'));
    }

    /**
     * Update the specified news category.
     */
    public function update(Request $request, NewsCategory $newsCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:news_categories,slug,' . $newsCategory->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:news_categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'language' => 'required|string|max:5',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string'
        ]);

        // Prevent self-parenting
        if ($validated['parent_id'] == $newsCategory->id) {
            $validated['parent_id'] = null;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default values
        $validated['is_active'] = $validated['is_active'] ?? true;

        DB::beginTransaction();
        try {
            $newsCategory->update($validated);

            // Log activity
            activity_log('news_category_updated', $newsCategory, [
                'name' => $newsCategory->name
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'Cập nhật chuyên mục thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật chuyên mục: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified news category.
     */
    public function destroy(NewsCategory $newsCategory)
    {
        // Check if category has children
        if ($newsCategory->children()->count() > 0) {
            return back()->with('error', 'Không thể xóa chuyên mục có con. Vui lòng xóa các chuyên mục con trước!');
        }

        // Check if category has news
        if ($newsCategory->news()->count() > 0) {
            return back()->with('error', 'Không thể xóa chuyên mục có tin tức. Vui lòng chuyển tin tức sang chuyên mục khác!');
        }

        DB::beginTransaction();
        try {
            $name = $newsCategory->name;
            
            $newsCategory->delete();

            // Log activity
            activity_log('news_category_deleted', null, [
                'name' => $name
            ]);

            DB::commit();

            return redirect()
                ->route('admin.news-categories.index')
                ->with('success', 'Xóa chuyên mục thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi xóa chuyên mục: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status (AJAX)
     */
    public function toggleStatus(NewsCategory $newsCategory)
    {
        try {
            $newsCategory->is_active = !$newsCategory->is_active;
            $newsCategory->save();
            
            $status = $newsCategory->is_active ? 'kích hoạt' : 'vô hiệu';
            
            return response()->json([
                'success' => true,
                'message' => "Chuyên mục đã được {$status}!",
                'is_active' => $newsCategory->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái!'
            ], 500);
        }
    }

    /**
     * Update order (AJAX)
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:news_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['categories'] as $categoryData) {
                NewsCategory::where('id', $categoryData['id'])
                    ->update(['sort_order' => $categoryData['sort_order']]);
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
                'message' => 'Lỗi khi cập nhật thứ tự!'
            ], 500);
        }
    }

    /**
     * Get categories as JSON (for AJAX)
     */
    public function json(Request $request)
    {
        $categories = NewsCategory::active()
            ->byLanguage($request->get('language', 'vi'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'parent_id']);

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Build tree structure from flat array
     */
    private function buildTree(array $elements, $parentId = null)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                $element['children'] = $children;
                $branch[] = $element;
            }
        }

        return $branch;
    }
}
