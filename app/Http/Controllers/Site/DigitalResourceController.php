<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use App\Models\SystemSetting;
use App\Models\SiteNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DigitalResourceController extends Controller
{
    /**
     * Kiểm tra quyền tải tài liệu của user hiện tại
     */
    private function canDownload(): bool
    {
        if (!Auth::check()) return false;

        $allowedGroups = json_decode(SystemSetting::get('digital_download_allowed_groups', '[]'), true) ?: [];
        $userGroupId   = Auth::user()->patronDetail?->patron_group_id;

        return $userGroupId && in_array($userGroupId, $allowedGroups);
    }

    public function show($id)
    {
        $resource = DigitalResource::with('folder')->findOrFail($id);

        // Tăng lượt xem (sử dụng view_count để đồng bộ với Admin)
        $resource->increment('view_count');

        // Get common data for site layout
        $menuItems   = SiteNode::getMenuItems('menu');
        $footerItems = SiteNode::getMenuItems('footer');

        // Get the parent node (Digital Library) for breadcrumb and sidebar
        $node = SiteNode::where('node_code', 'tai-lieu-so')->first();

        return view('site.pages.digital-resource-detail', compact('resource', 'menuItems', 'footerItems', 'node'));
    }

    public function viewPdf($id)
    {
        $resource = DigitalResource::findOrFail($id);

        // Get common data for site layout
        $menuItems   = SiteNode::getMenuItems('menu');
        $footerItems = SiteNode::getMenuItems('footer');

        // Get the parent node (Digital Library) for breadcrumb and sidebar
        $node = SiteNode::where('node_code', 'tai-lieu-so')->first();

        return view('site.pages.digital-resource-view', compact('resource', 'menuItems', 'footerItems', 'node'));
    }

    /**
     * Download digital resource
     * Chỉ cho phép nhóm độc giả có quyền tải (ví dụ: Giáo viên)
     */
    public function download($id)
    {
        // ✅ KIỂM TRA QUYỀN: chỉ nhóm được cấu hình mới được tải
        if (!$this->canDownload()) {
            abort(403, 'Bạn không có quyền tải tài liệu này.');
        }

        $resource = DigitalResource::findOrFail($id);

        // Tăng lượt tải
        $resource->increment('download_count');

        $filePath = storage_path('app/public/' . $resource->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $resource->file_name);
    }

    /**
     * Stream PDF đã mã hóa bằng XOR cipher (key dựa theo session)
     * Người dùng có quyền Download → stream thẳng file gốc (không mã hóa, dùng iframe)
     * Người dùng không có quyền → stream file đã mã hóa để PDF.js giải mã client-side
     */
    public function streamPdf($id)
    {
        try {
            $resource = DigitalResource::findOrFail($id);

            // Đường dẫn file PDF trong storage
            $filePath = storage_path('app/public/' . $resource->file_path);

            \Log::info('streamPdf attempt', ['id' => $id, 'file_path' => $filePath, 'exists' => file_exists($filePath)]);

            if (!file_exists($filePath)) {
                \Log::error('PDF file not found', ['id' => $id, 'file_path' => $filePath]);
                abort(404, 'File not found');
            }

            // Người có quyền download: stream file gốc thẳng (dùng cho iframe)
            if ($this->canDownload()) {
                \Log::info('streamPdf direct (has download permission)', ['id' => $id]);
                return response()->file($filePath, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $resource->file_name . '"',
                    'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                ]);
            }

            // Người không có quyền: mã hóa XOR + stream binary
            \Log::info('streamPdf encrypting (no download permission)', ['id' => $id, 'file_size' => filesize($filePath)]);

            $pdfBytes  = file_get_contents($filePath);
            $key       = $this->getEncryptionKey();
            $encrypted = $this->xorEncrypt($pdfBytes, $key);

            return response($encrypted, 200, [
                'Content-Type'        => 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'X-Enc-Mode'          => 'xor-session',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
            ]);
        } catch (\Exception $e) {
            \Log::error('streamPdf error', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Endpoint trả về encryption key cho client (dành cho user đang đăng nhập)
     * Key gắn với session để mỗi phiên đăng nhập có key riêng
     */
    public function getStreamKey($id)
    {
        // Không cho người có quyền download lấy key (họ dùng stream thẳng)
        if ($this->canDownload()) {
            return response()->json(['error' => 'Not needed'], 403);
        }

        $key = $this->getEncryptionKey();

        // Chỉ trả về key nếu đã đăng nhập (guest vẫn dùng key session)
        return response()->json([
            'key' => base64_encode($key),
        ]);
    }

    /**
     * Lấy encryption key từ session (tạo mới nếu chưa có)
     */
    private function getEncryptionKey(): string
    {
        $sessionKey = 'pdf_enc_key_' . (Auth::id() ?? 'guest');

        if (!session()->has($sessionKey)) {
            // Tạo key ngẫu nhiên 32 bytes và lưu vào session
            session([$sessionKey => random_bytes(32)]);
        }

        return session($sessionKey);
    }

    private function xorEncrypt(string $data, string $key): string
    {
        $keyLen = strlen($key);
        if ($keyLen === 0) {
            return $data;
        }

        // Lặp lại key để có độ dài bằng hoặc lớn hơn $data
        $keyRepeated = str_repeat($key, ceil(strlen($data) / $keyLen));
        // Cắt key cho bằng độ dài $data để thực hiện bitwise XOR trực tiếp
        $keyRepeated = substr($keyRepeated, 0, strlen($data));

        return $data ^ $keyRepeated;
    }
}
