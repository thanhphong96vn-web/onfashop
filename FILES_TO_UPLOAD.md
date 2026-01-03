# Danh sách file PHP cần cập nhật lên hosting

## 📋 Tổng quan
Các file PHP sau đây đã được thay đổi và cần upload lên hosting:

---

## 1. `app/Http/Resources/ConversationResource.php`
**Thay đổi:**
- ✅ Thêm field `receiver_shop` vào API response để frontend có thể group conversations theo shop
- ✅ Xử lý null cho `latest_message_time` để tránh lỗi khi conversation không có message

**Đường dẫn trên hosting:**
```
app/Http/Resources/ConversationResource.php
```

**Nội dung thay đổi:**
- Dòng 26: Thêm `'receiver_shop' => optional($this->receiver->shop)->name,`
- Dòng 31: Sửa `latest_message_time` để xử lý null: `$this->messages()->latest()->first() ? date('h:i:m d-m-Y', strtotime($this->messages()->latest()->first()->created_at)) : null,`

---

## 2. `app/Http/Helpers.php`
**Thay đổi:**
- ✅ Sửa function `static_asset()` để xử lý local environment (thêm prefix `public/` cho local)
- ✅ Trên production (cPanel), không thêm prefix vì DocumentRoot đã trỏ vào `public/`

**Đường dẫn trên hosting:**
```
app/Http/Helpers.php
```

**Nội dung thay đổi:**
- Dòng 783-791: Function `static_asset()` được sửa để:
  - Kiểm tra `app()->environment('local')`
  - Nếu local: thêm prefix `public/` vào path
  - Nếu production: giữ nguyên path (không thêm prefix)

**Code:**
```php
function static_asset($path, $secure = null)
{
    // Only add 'public/' prefix for local development (subdirectory setup)
    // On cPanel, DocumentRoot points to public/, so no prefix needed
    if (app()->environment('local')) {
        return app('url')->asset('public/' . $path, $secure);
    }
    return app('url')->asset($path, $secure);
}
```

---

## 📝 Lưu ý khi upload

1. **Backup trước khi upload:**
   - Backup các file cũ trước khi thay thế
   - Có thể rollback nếu có vấn đề

2. **Kiểm tra sau khi upload:**
   - Clear cache: `php artisan config:clear` và `php artisan cache:clear`
   - Kiểm tra API endpoint `/api/v1/user/querries` có trả về `receiver_shop` không
   - Kiểm tra admin dashboard có load CSS/JS đúng không

3. **File JavaScript/Vue:**
   - File `resources/js/components/inc/SellerChat.vue` cũng cần build và upload
   - Chạy `npm run build` để build frontend
   - Upload thư mục `public/build/` sau khi build

---

## ✅ Checklist

- [ ] Backup các file cũ
- [ ] Upload `app/Http/Resources/ConversationResource.php`
- [ ] Upload `app/Http/Helpers.php`
- [ ] Build frontend: `npm run build`
- [ ] Upload thư mục `public/build/` (nếu có thay đổi)
- [ ] Clear cache trên hosting: `php artisan config:clear` và `php artisan cache:clear`
- [ ] Test API endpoint `/api/v1/user/querries`
- [ ] Test chat feature - kiểm tra không còn duplicate tabs

---

## 🔍 Cách kiểm tra sau khi upload

1. **Kiểm tra API:**
   - Mở: `https://yourdomain.com/api/v1/user/querries`
   - Kiểm tra response có field `receiver_shop` trong mỗi conversation

2. **Kiểm tra Chat:**
   - Mở chat với shop
   - Kiểm tra sidebar chỉ có 1 tab cho mỗi shop (không duplicate)

3. **Kiểm tra Admin Dashboard:**
   - Đăng nhập admin
   - Kiểm tra CSS/JS load đúng

---

**Ngày tạo:** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
**Phiên bản:** 1.0

