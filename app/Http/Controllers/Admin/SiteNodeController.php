<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteNodeController extends Controller
{
    /**
     * Display a listing of site nodes.
     */
    public function index(Request $request)
    {
        $language = session('locale', app()->getLocale());
        $nodes = SiteNode::with(['parent', 'children'])
            ->byLanguage($language)
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get()
            ->sortBy('sort_order');

        // Build tree structure
        $tree = $this->buildTree($nodes->toArray());

        // Calculate statistics
        $stats = [
            'total' => SiteNode::count(),
            'published' => SiteNode::where('is_active', 1)->count(),
            'draft' => SiteNode::where('is_active', 0)->count(),
        ];

        return view('admin.site-nodes.index', compact('tree', 'language', 'stats'));
    }

    /**
     * Show the form for creating a new site node.
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');
        $language = session('locale', app()->getLocale());
        
        $parents = SiteNode::whereNull('parent_id')
            ->byLanguage($language)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->pluck('display_name', 'id');

        $routes = $this->getAvailableRoutes();
        $displayTypes = array_map('strval', [
            'menu' => 'Primary Menu',
            'sidebar' => 'Sidebar',
            'footer' => 'Footer',
            'page' => 'Content Page',
            'hidden' => 'Hidden'
        ]);

        $accessTypes = array_map('strval', [
            'public' => 'Public',
            'auth' => 'Authentication Required',
            'roles' => 'Role Based'
        ]);

        return view('admin.site-nodes.create', compact(
            'parents', 'routes', 'displayTypes', 'accessTypes', 'parentId', 'language'
        ));
    }

    /**
     * Store a newly created site node.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'node_code' => 'required|string|max:50|unique:site_nodes,node_code',
            'node_name' => 'required|string|max:100',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:site_nodes,id',
            'icon' => 'nullable|string|max:100',
            'masterpage' => 'nullable|string|max:100',
            'display_type' => 'required|in:menu,sidebar,footer,hidden,page',
            'target' => 'required|in:_self,_blank',
            'is_active' => 'nullable|string|in:on,off,1,0,yes,no',
            'access_type' => 'required|in:public,auth,roles',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string',
            'allow_guest' => 'nullable|string|in:on,off,1,0,yes,no',
            'items_data' => 'nullable|string',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'redirect_to' => 'nullable|string|max:255',
            'sort_order' => 'integer|min:0',
            'language' => 'nullable|string|max:5',
            'display_name_en' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255'
        ]);

        // Generate node_code if not provided
        if (empty($validated['node_code'])) {
            $validated['node_code'] = Str::slug($validated['display_name']);
        }

        // Set default values
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['allow_guest'] = $validated['allow_guest'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['language'] = $validated['language'] ?? session('locale', app()->getLocale());

        // Handle allowed_roles for role-based access
        if ($validated['access_type'] === 'roles' && !empty($validated['allowed_roles'])) {
            $validated['allowed_roles'] = json_encode($validated['allowed_roles']);
        } else {
            $validated['allowed_roles'] = null;
        }

        DB::beginTransaction();
        try {
            $node = SiteNode::create($validated);

            // Handle items_data for page nodes
            if ($node->display_type === 'page' && !empty($validated['items_data'])) {
                $itemsData = json_decode($validated['items_data'], true);
                if (is_array($itemsData)) {
                    foreach ($itemsData as $itemData) {
                        $node->items()->create([
                            'item_type' => $itemData['item_type'],
                            'item_data' => $itemData['item_data'],
                            'sort_order' => $itemData['sort_order']
                        ]);
                    }
                }
            }

            // Log activity
            activity_log('site_node_created', $node, [
                'display_name' => $node->display_name,
                'node_code' => $node->node_code
            ]);

            DB::commit();

            return redirect()
                ->route('admin.site-nodes.index', ['language' => $node->language])
                ->with('success', 'Tạo node thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Lỗi khi tạo node: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified site node.
     */
    public function edit(SiteNode $siteNode)
    {
        $parents = SiteNode::whereNull('parent_id')
            ->byLanguage($siteNode->language)
            ->where('id', '!=', $siteNode->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->pluck('display_name', 'id');

        $routes = $this->getAvailableRoutes();
        $displayTypes = [
            'menu' => 'Menu chính',
            'sidebar' => 'Sidebar', 
            'footer' => 'Footer',
            'page' => 'Trang nội dung',
            'hidden' => 'Ẩn'
        ];

        $accessTypes = [
            'public' => 'Công khai',
            'auth' => 'Yêu cầu đăng nhập',
            'roles' => 'Theo vai trò'
        ];

        return view('admin.site-nodes.edit', compact(
            'siteNode', 'parents', 'routes', 'displayTypes', 'accessTypes'
        ));
    }

    /**
     * Update the specified site node.
     */
    public function update(Request $request, SiteNode $siteNode)
    {
        $validated = $request->validate([
            'node_code' => 'required|string|max:50|unique:site_nodes,node_code,' . $siteNode->id,
            'node_name' => 'required|string|max:100',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:site_nodes,id',
            'icon' => 'nullable|string|max:100',
            'masterpage' => 'nullable|string|max:100',
            'display_type' => 'required|in:menu,sidebar,footer,hidden,page',
            'target' => 'required|in:_self,_blank',
            'is_active' => 'nullable|string|in:on,off,1,0,yes,no',
            'access_type' => 'required|in:public,auth,roles',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string',
            'allow_guest' => 'nullable|string|in:on,off,1,0,yes,no',
            'items_data' => 'nullable|string',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'redirect_to' => 'nullable|string|max:255',
            'sort_order' => 'integer|min:0',
            'language' => 'nullable|string|max:5',
            'display_name_en' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255'
        ]);

        // Convert checkbox values to boolean
        $validated['is_active'] = in_array($validated['is_active'] ?? null, ['on', '1', 'yes'], true);
        $validated['allow_guest'] = in_array($validated['allow_guest'] ?? null, ['on', '1', 'yes'], true);
        $validated['language'] = $validated['language'] ?? $siteNode->language ?? session('locale', app()->getLocale());

        // Prevent self-parenting
        if ($validated['parent_id'] == $siteNode->id) {
            $validated['parent_id'] = null;
        }

        // Handle allowed_roles for role-based access
        if ($validated['access_type'] === 'roles' && !empty($validated['allowed_roles'])) {
            $validated['allowed_roles'] = json_encode($validated['allowed_roles']);
        } else {
            $validated['allowed_roles'] = null;
        }

        DB::beginTransaction();
        try {
            $siteNode->update($validated);

            // Handle items_data for page nodes
            if ($siteNode->display_type === 'page' && !empty($validated['items_data'])) {
                $itemsData = json_decode($validated['items_data'], true);
                if (is_array($itemsData)) {
                    // Get existing items
                    $existingItemIds = $siteNode->items()->pluck('id')->toArray();
                    $updateItemIds = [];

                    foreach ($itemsData as $itemData) {
                        if (!empty($itemData['id'])) {
                            // Update existing item
                            $item = $siteNode->items()->find($itemData['id']);
                            if ($item) {
                                $item->update([
                                    'item_type' => $itemData['item_type'],
                                    'item_data' => $itemData['item_data'],
                                    'sort_order' => $itemData['sort_order']
                                ]);
                                $updateItemIds[] = $item->id;
                            }
                        } else {
                            // Create new item
                            $siteNode->items()->create([
                                'item_type' => $itemData['item_type'],
                                'item_data' => $itemData['item_data'],
                                'sort_order' => $itemData['sort_order']
                            ]);
                        }
                    }

                    // Delete items that are no longer in the list
                    $itemsToDelete = array_diff($existingItemIds, $updateItemIds);
                    if (!empty($itemsToDelete)) {
                        $siteNode->items()->whereIn('id', $itemsToDelete)->delete();
                    }
                }
            } elseif ($siteNode->display_type === 'page') {
                // If no items_data provided, delete all items for this node
                $siteNode->items()->delete();
            }

            // Log activity
            activity_log('site_node_updated', $siteNode, [
                'display_name' => $siteNode->display_name,
                'node_code' => $siteNode->node_code
            ]);

            DB::commit();

            return redirect()
                ->route('admin.site-nodes.index', ['language' => $siteNode->language])
                ->with('success', 'Cập nhật node thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Lỗi khi cập nhật node: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified site node.
     */
    public function destroy(SiteNode $siteNode)
    {
        // Check if node has children
        if ($siteNode->children()->count() > 0) {
            return back()->with('error', 'Không thể xóa node có con. Vui lòng xóa các node con trước!');
        }

        DB::beginTransaction();
        try {
            $nodeName = $siteNode->display_name;
            $language = $siteNode->language;
            
            $siteNode->delete();

            // Log activity
            activity_log('site_node_deleted', null, [
                'display_name' => $nodeName,
                'node_code' => $siteNode->node_code
            ]);

            DB::commit();

            return redirect()
                ->route('admin.site-nodes.index', ['language' => $language])
                ->with('success', 'Xóa node thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi xóa node: ' . $e->getMessage());
        }
    }

    /**
     * Update node order (AJAX)
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'nodes' => 'required|array',
            'nodes.*.id' => 'required|exists:site_nodes,id',
            'nodes.*.sort_order' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['nodes'] as $nodeData) {
                SiteNode::where('id', $nodeData['id'])
                    ->update(['sort_order' => $nodeData['sort_order']]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi khi cập nhật thứ tự!'], 500);
        }
    }

    /**
     * Toggle node status (AJAX)
     */
    public function toggleStatus(SiteNode $siteNode)
    {
        try {
            $siteNode->update(['is_active' => !$siteNode->is_active]);
            
            $status = $siteNode->is_active ? 'kích hoạt' : 'vô hiệu';
            
            return response()->json([
                'success' => true, 
                'message' => "Node đã được {$status}!",
                'is_active' => $siteNode->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái!'], 500);
        }
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
                // Add has_content flag
                $element['has_content'] = !empty($element['route_name']) || !empty($element['url']) || ($element['display_type'] === 'page');
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Display tree structure view
     */
    public function tree(Request $request)
    {
        $language = $request->get('language', 'vi');
        $nodes = SiteNode::with(['parent', 'children'])
            ->byLanguage($language)
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get();

        $tree = $this->buildTree($nodes->toArray());

        return view('admin.site-nodes.tree', compact('tree', 'language'));
    }

    /**
     * Get tree structure as JSON (for AJAX)
     */
    public function treeJson(Request $request)
    {
        $language = $request->get('language', 'vi');
        $tree = SiteNode::getTree($language);

        return response()->json([
            'success' => true,
            'tree' => $tree
        ]);
    }

    /**
     * Move node to new parent (AJAX)
     */
    public function moveNode(Request $request)
    {
        $validated = $request->validate([
            'node_id' => 'required|exists:site_nodes,id',
            'parent_id' => 'nullable|exists:site_nodes,id',
            'position' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $node = SiteNode::findOrFail($validated['node_id']);
            
            // Prevent self-parenting
            if ($validated['parent_id'] == $node->id) {
                throw new \Exception('Không thể đặt node làm con của chính nó');
            }

            // Update parent
            $node->parent_id = $validated['parent_id'];
            $node->sort_order = $validated['position'];
            $node->save();

            // Reorder siblings
            $this->reorderSiblings($validated['parent_id'], $node->id, $validated['position']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Di chuyển node thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi di chuyển node: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rebuild entire tree structure
     */
    public function rebuildTree(Request $request)
    {
        $validated = $request->validate([
            'tree' => 'required|array',
            'tree.*.id' => 'required|exists:site_nodes,id',
            'tree.*.parent_id' => 'nullable|exists:site_nodes,id',
            'tree.*.sort_order' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['tree'] as $nodeData) {
                SiteNode::where('id', $nodeData['id'])
                    ->update([
                        'parent_id' => $nodeData['parent_id'],
                        'sort_order' => $nodeData['sort_order']
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cấu trúc cây đã được cập nhật!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật cấu trúc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a node
     */
    public function duplicate(SiteNode $siteNode)
    {
        DB::beginTransaction();
        try {
            $newNode = $siteNode->replicate();
            $newNode->node_code = $siteNode->node_code . '_copy_' . time();
            $newNode->display_name = $siteNode->display_name . ' (Bản sao)';
            $newNode->node_name = $siteNode->node_name . '_copy';
            $newNode->is_active = false; // Start as inactive
            $newNode->save();

            // Log activity
            activity_log('site_node_duplicated', $newNode, [
                'original_id' => $siteNode->id,
                'display_name' => $newNode->display_name
            ]);

            DB::commit();

            return redirect()
                ->route('admin.site-nodes.edit', $newNode)
                ->with('success', 'Node đã được sao chép thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi sao chép node: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions on multiple nodes
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete,move',
            'node_ids' => 'required|array',
            'node_ids.*' => 'exists:site_nodes,id',
            'target_parent_id' => 'nullable|exists:site_nodes,id'
        ]);

        DB::beginTransaction();
        try {
            $nodeIds = $validated['node_ids'];
            $count = 0;

            switch ($validated['action']) {
                case 'activate':
                    SiteNode::whereIn('id', $nodeIds)->update(['is_active' => true]);
                    $count = count($nodeIds);
                    $message = "Đã kích hoạt {$count} node";
                    break;

                case 'deactivate':
                    SiteNode::whereIn('id', $nodeIds)->update(['is_active' => false]);
                    $count = count($nodeIds);
                    $message = "Đã vô hiệu hóa {$count} node";
                    break;

                case 'delete':
                    // Check for children first
                    $nodesWithChildren = SiteNode::whereIn('parent_id', $nodeIds)->exists();
                    if ($nodesWithChildren) {
                        throw new \Exception('Không thể xóa node có con. Vui lòng xóa các node con trước!');
                    }
                    
                    SiteNode::whereIn('id', $nodeIds)->delete();
                    $count = count($nodeIds);
                    $message = "Đã xóa {$count} node";
                    break;

                case 'move':
                    if (!$validated['target_parent_id']) {
                        throw new \Exception('Vui lòng chọn node đích');
                    }
                    
                    SiteNode::whereIn('id', $nodeIds)
                        ->update(['parent_id' => $validated['target_parent_id']]);
                    $count = count($nodeIds);
                    $message = "Đã di chuyển {$count} node";
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
     * Reorder siblings after moving a node
     */
    private function reorderSiblings($parentId, $excludeNodeId, $position)
    {
        $siblings = SiteNode::where('parent_id', $parentId)
            ->where('id', '!=', $excludeNodeId)
            ->orderBy('sort_order')
            ->get();

        $newOrder = $position;
        foreach ($siblings as $sibling) {
            if ($newOrder == $position) {
                $newOrder++;
            }
            $sibling->sort_order = $newOrder;
            $sibling->save();
            $newOrder++;
        }
    }

    /**
     * Get available routes for linking
     */
    private function getAvailableRoutes()
    {
        // Define available routes for public site
        return [
            'home' => 'Trang chủ',
            'opac.search' => 'Tra cứu OPAC',
            'patrons.register' => 'Đăng ký thẻ',
            'circulation.history' => 'Lịch sử mượn trả',
            'news.index' => 'Tin tức',
            'events.index' => 'Sự kiện',
            'contact.index' => 'Liên hệ',
            'help.index' => 'Trợ giúp'
        ];
    }

    public function updateLayoutSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp,ico,gif,apng|max:20480',
            'book_intro_image' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp,gif,apng|max:20480',
        ]);

        \App\Models\SystemSetting::set('site_name', $request->input('site_name', ''), 'site');

        // Handle Logo
        if ($request->hasFile('site_logo')) {
            $oldLogo = \App\Models\SystemSetting::get('site_logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('site_logo')->store('site', 'public');
            \App\Models\SystemSetting::set('site_logo', $path, 'site');
        }

        if ($request->boolean('remove_logo') && !$request->hasFile('site_logo')) {
            $oldLogo = \App\Models\SystemSetting::get('site_logo');
            if ($oldLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            \App\Models\SystemSetting::set('site_logo', '', 'site');
        }

        // Handle Book Intro Image
        if ($request->hasFile('book_intro_image')) {
            $oldImg = \App\Models\SystemSetting::get('book_intro_image');
            if ($oldImg && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldImg)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImg);
            }

            $path = $request->file('book_intro_image')->store('site', 'public');
            \App\Models\SystemSetting::set('book_intro_image', $path, 'site');
        }

        if ($request->boolean('remove_book_intro') && !$request->hasFile('book_intro_image')) {
            $oldImg = \App\Models\SystemSetting::get('book_intro_image');
            if ($oldImg && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldImg)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImg);
            }
            \App\Models\SystemSetting::set('book_intro_image', '', 'site');
        }

        return back()->with('success', __('Layout settings updated successfully.'));
    }

    /**
     * Add a new library network logo
     */
    public function addNetworkLogo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'logo_path' => 'required|file|mimes:png,jpg,jpeg,svg,webp,ico,gif,apng,avif|max:20480',
        ]);

        try {
            // Store the logo file
            $logoPath = $request->file('logo_path')->store('network-logos', 'public');

            // Create library network logo record
            $logo = \App\Models\LibraryNetworkLogo::create([
                'name' => $validated['name'],
                'url' => $validated['url'],
                'logo_path' => $logoPath,
                'sort_order' => \App\Models\LibraryNetworkLogo::max('sort_order') + 1,
                'is_active' => true
            ]);

            // Log activity
            activity_log('library_network_logo_created', $logo, [
                'name' => $logo->name,
                'url' => $logo->url
            ]);

            return back()->with('success', __('Thêm nhãn hiệu thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi thêm nhãn hiệu: ' . $e->getMessage());
        }
    }

    /**
     * Edit a library network logo
     */
    public function editNetworkLogo(\App\Models\LibraryNetworkLogo $logo)
    {
        return response()->json($logo);
    }

    /**
     * Update a library network logo
     */
    public function updateNetworkLogo(Request $request, \App\Models\LibraryNetworkLogo $logo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'logo_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp,ico,gif,apng,avif|max:20480',
        ]);

        try {
            // If new logo file is uploaded, delete old one
            if ($request->hasFile('logo_path')) {
                if ($logo->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo->logo_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($logo->logo_path);
                }
                $validated['logo_path'] = $request->file('logo_path')->store('network-logos', 'public');
            }

            $logo->update($validated);

            // Log activity
            activity_log('library_network_logo_updated', $logo, [
                'name' => $logo->name,
                'url' => $logo->url
            ]);

            return back()->with('success', __('Cập nhật nhãn hiệu thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi cập nhật nhãn hiệu: ' . $e->getMessage());
        }
    }

    /**
     * Delete a library network logo
     */
    public function deleteNetworkLogo(\App\Models\LibraryNetworkLogo $logo)
    {
        try {
            // Delete the logo file
            if ($logo->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($logo->logo_path);
            }

            // Log activity
            activity_log('library_network_logo_deleted', null, [
                'name' => $logo->name,
                'url' => $logo->url
            ]);

            $logo->delete();

            return back()->with('success', __('Xóa nhãn hiệu thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa nhãn hiệu: ' . $e->getMessage());
        }
    }

    /**
     * Add a new banner
     */
    public function addBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'link_url' => 'required|url|max:255',
            'image_path' => 'required|file|mimes:png,jpg,jpeg,svg,webp,gif,apng,avif|max:20480',
        ]);

        try {
            // Store the banner image file
            $imagePath = $request->file('image_path')->store('banners', 'public');

            // Create banner record
            $banner = \App\Models\Banner::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'link_url' => $validated['link_url'],
                'image_url' => $imagePath,
                'status' => 'active',
                'position' => 'home_hero',
                'language' => session('locale', app()->getLocale()),
                'sort_order' => \App\Models\Banner::max('sort_order') + 1,
            ]);

            // Log activity
            activity_log('banner_created', $banner, [
                'title' => $banner->title,
                'link_url' => $banner->link_url
            ]);

            return back()->with('success', __('Thêm banner thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi thêm banner: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing banner
     */
    public function updateBanner(Request $request, \App\Models\Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'link_url' => 'required|url|max:255',
            'image_path' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp,gif,apng,avif|max:20480',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'link_url' => $validated['link_url'],
            ];

            if ($request->hasFile('image_path')) {
                if ($banner->image_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->image_url)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image_url);
                }
                $data['image_url'] = $request->file('image_path')->store('banners', 'public');
            }

            $banner->update($data);

            activity_log('banner_updated', $banner, [
                'title' => $banner->title,
                'link_url' => $banner->link_url
            ]);

            return back()->with('success', __('Cập nhật banner thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi cập nhật banner: ' . $e->getMessage());
        }
    }

    /**
     * Delete a banner
     */
    public function deleteBanner(\App\Models\Banner $banner)
    {
        try {
            // Delete the image file
            if ($banner->image_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->image_url)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image_url);
            }

            // Log activity
            activity_log('banner_deleted', null, [
                'title' => $banner->title,
                'link_url' => $banner->link_url
            ]);

            $banner->delete();

            return back()->with('success', __('Xóa banner thành công!'));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa banner: ' . $e->getMessage());
        }
    }

    /**
     * Toggle banner status (active/inactive)
     */
    public function toggleBannerStatus(\App\Models\Banner $banner)
    {
        try {
            $newStatus = ($banner->status === 'active') ? 'inactive' : 'active';
            $banner->update(['status' => $newStatus]);

            // Log activity
            activity_log('banner_status_toggled', $banner, [
                'title' => $banner->title,
                'status' => $newStatus
            ]);

            if (request()->wantsJson() || request()->ajax() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => __('Cập nhật trạng thái banner thành công!'),
                    'status' => $newStatus,
                    'is_active' => ($newStatus === 'active')
                ]);
            }

            return back()->with('success', __('Cập nhật trạng thái banner thành công!'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật trạng thái banner: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Lỗi khi cập nhật trạng thái banner: ' . $e->getMessage());
        }
    }
}
