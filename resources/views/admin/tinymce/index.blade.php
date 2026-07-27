@extends('layouts.admin')

@section('title', 'Quản lý TinyMCE Token')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h1 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-key text-blue-600"></i>
                    Quản lý TinyMCE Token
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Cập nhật API key cho TinyMCE Editor
                </p>
            </div>

            <div class="p-6">
                <!-- Current Token Display -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Token hiện tại:
                    </label>
                    <div class="font-mono text-sm bg-white p-3 rounded border" id="current-token-display">
                        {{ $currentToken ?: 'Chưa có token' }}
                    </div>
                </div>

                <!-- Update Form -->
                <form id="token-update-form" class="space-y-4">
                    @csrf
                    <div>
                        <label for="token" class="block text-sm font-medium text-gray-700 mb-2">
                            Token mới:
                        </label>
                        <input 
                            type="text" 
                            id="token" 
                            name="token" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập TinyMCE API key mới"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Lấy API key từ <a href="https://www.tiny.cloud/my-account/dashboard/" target="_blank" class="text-blue-600 hover:underline">TinyMCE Dashboard</a>
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            id="submit-btn"
                        >
                            <i class="fas fa-save"></i>
                            Cập nhật Token
                        </button>
                        
                        <button 
                            type="button" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            onclick="clearForm()"
                        >
                            <i class="fas fa-times"></i>
                            Hủy
                        </button>
                    </div>
                </form>

                <!-- Instructions -->
                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle"></i>
                        Hướng dẫn:
                    </h3>
                    <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                        <li>Truy cập <a href="https://www.tiny.cloud/my-account/dashboard/" target="_blank" class="underline">TinyMCE Dashboard</a></li>
                        <li>Đăng nhập hoặc tạo tài khoản miễn phí</li>
                        <li>Copy API key từ dashboard</li>
                        <li>Paste vào form trên và nhấn "Cập nhật Token"</li>
                        <li>Token sẽ được lưu vào file .env và có hiệu lực ngay lập tức</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" class="fixed top-4 right-4 z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('token-update-form');
    const submitBtn = document.getElementById('submit-btn');
    const tokenInput = document.getElementById('token');
    const currentTokenDisplay = document.getElementById('current-token-display');
    const messageContainer = document.getElementById('message-container');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const token = tokenInput.value.trim();
        if (!token) {
            showMessage('Vui lòng nhập token', 'error');
            return;
        }

        // Disable form
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';

        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route("admin.tinymce.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showMessage(data.message, 'success');
                currentTokenDisplay.textContent = data.token;
                tokenInput.value = '';
            } else {
                showMessage(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showMessage('Lỗi kết nối: ' + error.message, 'error');
        } finally {
            // Re-enable form
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Cập nhật Token';
        }
    });
});

function clearForm() {
    document.getElementById('token').value = '';
}

function showMessage(message, type) {
    const messageContainer = document.getElementById('message-container');
    const messageDiv = document.createElement('div');
    
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    messageDiv.className = `${bgColor} px-4 py-3 rounded border mb-2 shadow-lg`;
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${icon} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg leading-none">×</button>
        </div>
    `;
    
    messageContainer.appendChild(messageDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}
</script>
@endsection