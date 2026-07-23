<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OerSubject;
use App\Models\OpenEducationalResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OerController extends Controller
{
    /**
     * Display a listing of OER items.
     */
    public function index(Request $request)
    {
        $subjects = OerSubject::where('is_active', true)->orderBy('sort_order')->get();

        $query = OpenEducationalResource::with('subject')->orderBy('sort_order', 'asc')->orderBy('id', 'desc');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('author', 'like', "%{$keyword}%")
                  ->orWhere('publisher', 'like', "%{$keyword}%");
            });
        }

        $resources = $query->paginate(20)->withQueryString();

        return view('admin.oer.index', compact('resources', 'subjects'));
    }

    /**
     * Store a newly created OER item.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:oer_subjects,id',
            'url' => 'required|url',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'license' => 'nullable|string|max:100',
        ]);

        $imagePath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/pages'), $filename);
            $imagePath = 'storage/pages/' . $filename;
        } elseif ($request->filled('thumbnail_url')) {
            $imagePath = $request->thumbnail_url;
        }

        OpenEducationalResource::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'author' => $request->author ?: 'Tài nguyên mở OER',
            'publisher' => $request->publisher ?: 'Cổng thông tin Thư viện VTTU',
            'description' => $request->description,
            'url' => $request->url,
            'thumbnail_url' => $imagePath ?: 'https://placehold.co/300x400/7B0000/FFFFFF?text=OER+BOOK',
            'license' => $request->license ?: 'CC BY-NC',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.oer.index')->with('success', 'Thêm mới tài nguyên giáo dục mở thành công!');
    }

    /**
     * Update the specified OER item.
     */
    public function update(Request $request, $id)
    {
        $resource = OpenEducationalResource::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:oer_subjects,id',
            'url' => 'required|url',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'license' => 'nullable|string|max:100',
        ]);

        $imagePath = $resource->thumbnail_url;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/pages'), $filename);
            $imagePath = 'storage/pages/' . $filename;
        } elseif ($request->filled('thumbnail_url')) {
            $imagePath = $request->thumbnail_url;
        }

        $resource->update([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'author' => $request->author ?: 'Tài nguyên mở OER',
            'publisher' => $request->publisher ?: 'Cổng thông tin Thư viện VTTU',
            'description' => $request->description,
            'url' => $request->url,
            'thumbnail_url' => $imagePath,
            'license' => $request->license ?: 'CC BY-NC',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.oer.index')->with('success', 'Cập nhật tài nguyên giáo dục mở thành công!');
    }

    /**
     * Remove the specified OER item.
     */
    public function destroy($id)
    {
        $resource = OpenEducationalResource::findOrFail($id);
        $resource->delete();

        return redirect()->route('admin.oer.index')->with('success', 'Xóa tài nguyên giáo dục mở thành công!');
    }
}
