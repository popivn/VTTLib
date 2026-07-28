# Mail Management System - Setup Guide

## 📋 Tổng Quan

Hệ thống quản lý email với 3 tab chính:
1. **Danh Sách Email** - Xem tất cả email đã gửi với filters
2. **Soạn Email** - Tạo và gửi email mới (đơn hoặc hàng loạt)
3. **Xem Trước Email** - Xem trước nội dung email trước khi gửi

---

## 🗂️ Cấu Trúc Files Được Tạo

### Controllers
- **MailManagementController** (`app/Http/Controllers/Admin/MailManagementController.php`)
  - Xử lý tất cả logic mail management
  - Methods: index, create, preview, show, send, sendMass, resend, destroy, bulkAction, statistics, export

### Views
```
resources/views/admin/mails/
├── index.blade.php                 # Trang chính với tabs
├── show.blade.php                  # Chi tiết email
└── tabs/
    ├── list.blade.php              # Tab danh sách email
    ├── create.blade.php            # Tab soạn email
    └── mail-view.blade.php         # Tab xem trước email
```

### Database
- **Migration**: `database/migrations/2026_07_28_000001_create_mail_logs_table.php`
- **Table**: `mail_logs` với các cột:
  - id (primary key)
  - recipient (email người nhận)
  - subject (chủ đề)
  - body (nội dung)
  - cc, bcc, reply_to
  - status (pending, sent, failed)
  - error_message
  - sent_at
  - timestamps

### Routes
```php
Route::prefix('mails')->name('admin.mails.')->group(function () {
    Route::get('/', 'index')->name('index');           # Danh sách
    Route::post('/send', 'send')->name('send');        # Gửi email
    Route::post('/send-mass', 'sendMass')->name('send-mass');  # Gửi hàng loạt
    Route::post('/preview', 'preview')->name('preview');        # Xem trước
    Route::get('/{mail}', 'show')->name('show');       # Chi tiết
    Route::post('/{mail}/resend', 'resend')->name('resend');   # Gửi lại
    Route::delete('/{mail}', 'destroy')->name('destroy');      # Xóa
    Route::post('/bulk-action', 'bulkAction')->name('bulk-action');  # Hành động hàng loạt
    Route::get('/statistics', 'statistics')->name('statistics');     # Thống kê
    Route::get('/export', 'export')->name('export');   # Xuất CSV
});
```

---

## 🚀 Cách Sử Dụng

### 1. Truy Cập Trang Mail Management
```
URL: /admin/mails
```

### 2. Tab Danh Sách Email
- Xem tất cả email đã gửi
- Filter theo: trạng thái, người nhận, chủ đề, ngày tạo
- Hành động: xem chi tiết, gửi lại, xóa

### 3. Tab Soạn Email
- **Email Đơn**: Soạn email cho 1 người
- **Email Hàng Loạt**: Gửi cho nhiều người (cách nhau bởi dấu phẩy)
- Hỗ trợ: CC, BCC, Reply-To
- Xem trước real-time khi soạn

### 4. Tab Xem Trước Email (Mail View)
- **Chọn Mẫu**: 5 mẫu email có sẵn
- **Chỉnh Sửa**: Tùy chỉnh nội dung
- **Xem Trước**: Hiển thị cách email sẽ trông như thế nào
- **Hành Động**: In, sao chép nội dung
- **Biến Có Sẵn**: {{name}}, {{email}}, {{date}}, {{library}}

---

## 📊 Thống Kê

Trang hiển thị 4 card chính:
- **Tổng Email**: Tất cả email
- **Đã Gửi**: Email gửi thành công
- **Thất Bại**: Email gửi thất bại
- **Đang Chờ**: Email chưa gửi

---

## 🔧 Configuration

### SMTP Settings
Đảm bảo `.env` đã cấu hình đúng:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@library.edu.vn"
MAIL_FROM_NAME="Thư viện Đại học"
```

---

## 📝 Features

### Soạn Email
- [x] Email đơn
- [x] Email hàng loạt
- [x] CC/BCC
- [x] Reply-To
- [x] Xem trước real-time
- [x] Validation

### Xem Trước (Mail View)
- [x] Mẫu email có sẵn
- [x] Hiển thị đẹp (Gmail-like)
- [x] Biến động
- [x] In document
- [x] Copy nội dung
- [x] Thay đổi real-time

### Quản Lý Email
- [x] Danh sách email với pagination
- [x] Filter/Search
- [x] Xem chi tiết
- [x] Gửi lại email
- [x] Xóa email
- [x] Thống kê
- [x] Export CSV

---

## 🎨 UI/UX

- **Dark Mode**: Hỗ trợ theme tối
- **Responsive**: Tối ưu mobile & desktop
- **Icons**: Sử dụng Lucide icons
- **Color Scheme**: Indigo, Green, Red, Amber

---

## 📌 Notes

1. **Migration**: Chạy `php artisan migrate` để tạo bảng `mail_logs`
2. **Routes**: Routes đã được thêm vào `routes/web.php`
3. **Controller**: File controller đã được tạo trong `app/Http/Controllers/Admin/`
4. **Views**: Tất cả views đã được tạo trong `resources/views/admin/mails/`

---

## 🔐 Security

- [x] CSRF protection
- [x] Email validation
- [x] Authorization (middleware admin)
- [x] Input sanitization
- [x] Error logging
- [x] Safe deletion

---

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. SMTP configuration trong `.env`
2. Database migration chạy thành công
3. Routes đã được register
4. Views tồn tại trong `resources/views/admin/mails/`

---

## 🚢 Next Steps (Optional)

- [ ] Thêm template editor (WYSIWYG)
- [ ] Scheduled emails
- [ ] Email tracking/analytics
- [ ] Attachment support
- [ ] Email campaign management
- [ ] Webhook integration
