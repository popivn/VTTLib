<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TinyMceController extends Controller
{
    /**
     * Show the TinyMCE configuration page
     */
    public function index()
    {
        $currentToken = env('TinyEMC', '');
        return view('admin.tinymce.index', compact('currentToken'));
    }

    /**
     * Update TinyMCE token in .env file
     */
    public function updateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:255'
        ]);

        $token = trim($request->input('token'));
        
        try {
            $this->updateEnvFile('TinyEMC', $token);
            
            return response()->json([
                'success' => true,
                'message' => 'TinyMCE token đã được cập nhật thành công!',
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update environment file with new value
     */
    private function updateEnvFile($key, $value)
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            throw new \Exception('File .env không tồn tại');
        }

        $content = File::get($envPath);
        
        // Check if key exists
        if (preg_match("/^{$key}=.*$/m", $content)) {
            // Update existing key
            $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
        } else {
            // Add new key at the end
            $content .= "\n{$key}={$value}";
        }

        File::put($envPath, $content);
    }
}