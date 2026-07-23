<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ClientLoginController extends Controller
{
    public function create(Request $request)
    {
        $isSsoError = $request->query('sso_error') == 1 || session()->has('error_message_sso');
        return view('auth.login', compact('isSsoError'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = $request->input('username');
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'username' => trans('auth.failed'),
        ]);
    }

    /**
     * Verify login via VTTU external API
     */
    public function verifyLoginByUsernameAndToken(Request $request): RedirectResponse
    {
        $username = $request->query('username');

        if (!$username) {
            \Illuminate\Support\Facades\Log::warning("SSO: Đăng nhập thất bại do thiếu tham số username.");
            return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Thiếu thông tin tài khoản username.');
        }

        \Illuminate\Support\Facades\Log::info("SSO: Khởi động quá trình xác thực cho username = {$username}");

        // 1. Kiểm tra nếu user đã tồn tại trong hệ thống thư viện
        $user = User::where('username', $username)->first();

        if (!$user) {
            \Illuminate\Support\Facades\Log::info("SSO: Tài khoản {$username} chưa tồn tại trên VTTLib. Khởi chạy gọi API xác thực đến Study.");

            // 2. Gọi API lấy thông tin người dùng từ Study
            $apiUrl = 'https://study.vttu.edu.vn/api/user-info';
            $queryParams = [
                'user_code' => $username,
                'key' => '13467902',
            ];
            $fullApiUrl = $apiUrl . '?' . http_build_query($queryParams);
            \Illuminate\Support\Facades\Log::info("SSO: Gọi API xác thực tại URL: {$fullApiUrl}");

            try {
                $response = Http::withoutVerifying()->get($apiUrl, $queryParams);

                \Illuminate\Support\Facades\Log::info("SSO: Kết quả API của {$username} - Mã trạng thái HTTP: " . $response->status());

                // Xử lý khi API trả về lỗi không được phép (Unauthorized) hoặc các mã lỗi 403, 401
                $isUnauthorized = false;
                if ($response->status() === 403 || $response->status() === 401) {
                    $isUnauthorized = true;
                } else {
                    $apiData = $response->json();
                    if (isset($apiData['error']) && $apiData['error'] === 'Unauthorized') {
                        $isUnauthorized = true;
                    }
                }

                if ($isUnauthorized) {
                    \Illuminate\Support\Facades\Log::warning("SSO: Xác thực thất bại cho {$username} do tài khoản không được cấp phép tại Study API.");

                    // Lưu log vào database
                    \App\Models\ActivityLog::create([
                        'action' => 'SSO_LOGIN_UNAUTHORIZED',
                        'method' => 'GET',
                        'url' => request()->fullUrl(),
                        'details' => ['username' => $username, 'status' => 'unauthorized', 'error' => 'User not found or key mismatch on Study'],
                        'ip_address' => request()->ip()
                    ]);

                    return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Bạn không có thông tin trên hệ thống vui lòng liên hệ TTCNPM Trường Đại học Võ Trường Toản - SDT: 02933504398. Cảm ơn.');
                }

                if ($response->successful()) {
                    $apiData = $response->json();

                    // Xác định vai trò ánh xạ từ API sang hệ thống thư viện
                    $dbRoleName = null;
                    $apiRoleId = $apiData['role_id'] ?? null;
                    if ($apiRoleId == 1) {
                        $dbRoleName = 'root';
                    } elseif ($apiRoleId == 2 || $apiRoleId == 12) {
                        $dbRoleName = 'teacher';
                    } elseif ($apiRoleId == 4) {
                        $dbRoleName = 'visitor';
                    }

                    if (!$dbRoleName) {
                        \Illuminate\Support\Facades\Log::warning("SSO: Hủy đăng nhập cho {$username}. Vai trò API role_id={$apiRoleId} không hợp lệ.");
                        return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Vai trò của bạn không được cấp phép truy cập hệ thống.');
                    }

                    $role = \App\Models\Role::where('name', $dbRoleName)->first();
                    if (!$role) {
                        \Illuminate\Support\Facades\Log::error("SSO: Vai trò '{$dbRoleName}' chưa được định nghĩa trong hệ thống.");
                        return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Hệ thống chưa cấu hình vai trò bảo mật tương ứng.');
                    }

                    // Tạo User mới trong hệ thống thư viện
                    $user = User::create([
                        'name' => $apiData['name'] ?? $username,
                        'username' => $username,
                        'email' => $apiData['email'] ?? ($username . '@vttu.edu.vn'),
                        'password' => bcrypt('Vttulib@2026'),
                        'status' => 'active',
                    ]);

                    // Gán vai trò vào bảng pivot role_user
                    $roleUserPivotId = \Illuminate\Support\Facades\DB::table('role_user')->insertGetId([
                        'role_id' => $role->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Đồng bộ các quyền sidebar tương ứng
                    $roleSidebars = \Illuminate\Support\Facades\DB::table('role_sidebars')->where('role_id', $role->id)->get();
                    foreach ($roleSidebars as $rs) {
                        \Illuminate\Support\Facades\DB::table('user_role_sidebars')->insert([
                            'role_user_id' => $roleUserPivotId,
                            'sidebar_id' => $rs->sidebar_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Tạo dữ liệu độc giả (PatronDetail)
                    $groupCode = ($apiRoleId == 4) ? 'st' : 'gv';
                    $patronGroup = \Illuminate\Support\Facades\DB::table('patron_groups')->where('code', $groupCode)->first();
                    if (!$patronGroup) {
                        $patronGroup = \Illuminate\Support\Facades\DB::table('patron_groups')->first();
                    }
                    $patronGroupId = $patronGroup ? $patronGroup->id : 2;

                    \Illuminate\Support\Facades\DB::table('patron_details')->insert([
                        'user_id' => $user->id,
                        'patron_group_id' => $patronGroupId,
                        'patron_code' => $username,
                        'display_name' => $user->name,
                        'card_status' => 'active',
                        'mssv' => $apiData['user_code'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    \Illuminate\Support\Facades\Log::info("SSO: Khởi tạo tài khoản tự động thành công cho {$username} (ID: {$user->id}, Vai trò: {$dbRoleName})");

                    // Lưu log khởi tạo thành công vào DB
                    \App\Models\ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'SSO_REGISTER_SUCCESS',
                        'method' => 'GET',
                        'url' => request()->fullUrl(),
                        'details' => ['username' => $username, 'role' => $dbRoleName, 'status' => 'created'],
                        'ip_address' => request()->ip()
                    ]);
                } else {
                    $responseBody = $response->body();
                    \Illuminate\Support\Facades\Log::error("SSO: API ({$fullApiUrl}) trả về lỗi không mong muốn cho {$username}. Chi tiết phản hồi (HTTP {$response->status()}): {$responseBody}");

                    // Lưu log lỗi vào DB
                    \App\Models\ActivityLog::create([
                        'action' => 'SSO_LOGIN_API_ERROR',
                        'method' => 'GET',
                        'url' => $fullApiUrl,
                        'details' => [
                            'username' => $username,
                            'status' => $response->status(),
                            'response' => $responseBody
                        ],
                        'ip_address' => request()->ip()
                    ]);

                    return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Lỗi kết nối đến hệ thống xác thực. Vui lòng thử lại sau.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("SSO: Lỗi hệ thống trong verifyLoginByUsernameAndToken cho {$username}: " . $e->getMessage());

                // Lưu log lỗi hệ thống vào DB
                \App\Models\ActivityLog::create([
                    'action' => 'SSO_LOGIN_ERROR',
                    'method' => 'GET',
                    'url' => request()->fullUrl(),
                    'details' => ['username' => $username, 'status' => 'error', 'message' => $e->getMessage()],
                    'ip_address' => request()->ip()
                ]);

                return redirect()->route('login', ['sso_error' => 1])->with('error_message_sso', 'Không thể kết nối đến hệ thống xác thực: ' . $e->getMessage());
            }
        } else {
            \Illuminate\Support\Facades\Log::info("SSO: Đăng nhập thành công cho người dùng đã tồn tại: {$username}");

            // Lưu log đăng nhập thành công cho user đã có sẵn
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'SSO_LOGIN_EXISTING',
                'method' => 'GET',
                'url' => request()->fullUrl(),
                'details' => ['username' => $username, 'status' => 'success'],
                'ip_address' => request()->ip()
            ]);
        }

        // 3. Tiến hành đăng nhập
        Auth::login($user);
        $request->session()->regenerate();

        // 4. Chuyển hướng người dùng dựa vào vai trò
        if ($user->hasRole('root') || $user->hasRole('admin')) {
            return redirect('/topsecret/dashboard');
        }

        return redirect('/');
    }

    /**
     * Bridge endpoint to proxy and test Study user-info API
     */
    public function getStudyUserInfo(Request $request)
    {
        $userCode = $request->query('user_code') ?: $request->query('username');
        if (!$userCode) {
            return response()->json(['error' => 'Missing user_code or username parameter'], 400);
        }

        $apiUrl = 'https://study.vttu.edu.vn/api/user-info';
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])
                ->get($apiUrl, [
                    'user_code' => $userCode,
                    'key' => '13467902',
                ]);

            return response()->json([
                'status' => $response->status(),
                'raw_body' => $response->body(),
                'json' => $response->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to connect to Study API',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
