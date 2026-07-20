<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumMajor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CurriculumController extends Controller
{
    /**
     * Display curriculum majors list in admin panel.
     */
    public function index()
    {
        $majors = CurriculumMajor::orderBy('sort_order', 'asc')->get();
        return view('admin.curriculum.index', compact('majors'));
    }

    /**
     * Store a new curriculum major.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:500',
        ], [
            'title.required' => 'Vui lòng nhập tên ngành đào tạo.',
        ]);

        $imagePath = $request->image_url;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('pages', 'public');
            $imagePath = '/storage/' . $path;
        }

        CurriculumMajor::create([
            'title' => $request->title,
            'description' => $request->description,
            'link_url' => $request->link_url,
            'image' => $imagePath,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return back()->with('success', 'Thêm ngành đào tạo mới thành công!');
    }

    /**
     * Update an existing curriculum major.
     */
    public function update(Request $request, $id)
    {
        $major = CurriculumMajor::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_url' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:500',
        ], [
            'title.required' => 'Vui lòng nhập tên ngành đào tạo.',
        ]);

        $imagePath = $major->image;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('pages', 'public');
            $imagePath = '/storage/' . $path;
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->image_url;
        }

        $major->update([
            'title' => $request->title,
            'description' => $request->description,
            'link_url' => $request->link_url,
            'image' => $imagePath,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : false,
        ]);

        return back()->with('success', 'Cập nhật ngành đào tạo thành công!');
    }

    /**
     * Delete a curriculum major.
     */
    public function destroy($id)
    {
        $major = CurriculumMajor::findOrFail($id);
        $major->delete();

        return back()->with('success', 'Đã xóa ngành đào tạo thành công!');
    }
}
