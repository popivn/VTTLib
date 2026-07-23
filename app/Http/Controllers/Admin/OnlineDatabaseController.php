<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnlineDatabase;
use Illuminate\Http\Request;

class OnlineDatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = OnlineDatabase::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $databases = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.online-databases.index', compact('databases', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.online-databases.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480',
            'image_url' => 'nullable|string|max:2048',
            'url' => 'nullable|string|max:2048',
            'hd_url' => 'nullable|string|max:2048',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $request->file('image_file')->store('online-databases', 'public');
        }

        OnlineDatabase::create($validated);

        return redirect()->route('admin.online-databases.index')
            ->with('success', 'Đã thêm cơ sở dữ liệu trực tuyến thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $database = OnlineDatabase::findOrFail($id);
        return view('admin.online-databases.edit', compact('database'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $database = OnlineDatabase::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,apng,avif|max:20480',
            'image_url' => 'nullable|string|max:2048',
            'url' => 'nullable|string|max:2048',
            'hd_url' => 'nullable|string|max:2048',
            'content' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image_file')) {
            $oldImage = $database->getRawOriginal('image_url');
            if (!empty($oldImage) && !str_starts_with($oldImage, 'http://') && !str_starts_with($oldImage, 'https://')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage);
            }
            $validated['image_url'] = $request->file('image_file')->store('online-databases', 'public');
        }

        $database->update($validated);

        return redirect()->route('admin.online-databases.index')
            ->with('success', 'Đã cập nhật cơ sở dữ liệu trực tuyến thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $database = OnlineDatabase::findOrFail($id);
        
        $oldImage = $database->getRawOriginal('image_url');
        if (!empty($oldImage) && !str_starts_with($oldImage, 'http://') && !str_starts_with($oldImage, 'https://')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage);
        }

        $database->delete();

        return redirect()->route('admin.online-databases.index')
            ->with('success', 'Đã xóa cơ sở dữ liệu trực tuyến thành công.');
    }
}
