# 📋 CHECKLIST TRƯỚC KHI UPLOAD LÊN CPANEL

## ✅ ĐÃ XÓA CÁC FILE KHÔNG CẦN THIẾT:
- ✅ `storage/framework/cache/data/*` - Cache files (đã xóa)
- ✅ `storage/framework/views/*.php` - View cache files (đã xóa)
- ✅ `storage/framework/sessions/*` - Session files (đã xóa)
- ✅ `storage/logs/*.log` - Log files (đã xóa)
- ✅ `public/hot` - Không tồn tại (OK)
- ✅ `storage/vite.hot` - Không tồn tại (OK)

## ⚠️ KHÔNG UPLOAD CÁC FILE/THỦ MỤC SAU LÊN SERVER:

### 1. Thư mục node_modules/
- **Lý do**: Quá lớn, không cần thiết trên server
- **Hành động**: Không upload, sẽ cài lại trên server bằng `npm install` (nếu cần)

### 2. File .env
- **Lý do**: Chứa thông tin nhạy cảm và cấu hình local
- **Hành động**: Tạo file `.env` mới trên server với cấu hình production

### 3. Thư mục .git/ (nếu có)
- **Lý do**: Không cần thiết trên production server
- **Hành động**: Không upload

### 4. Thư mục temp/
- **Lý do**: File tạm thời
- **Hành động**: Có thể xóa hoặc không upload

### 5. File debug/test:
- `phpunit.xml` - File test (có thể bỏ qua)
- `tests/` - Thư mục test (có thể bỏ qua)

## 📦 CÁC FILE/THỦ MỤC PHẢI UPLOAD:

### Bắt buộc:
- ✅ `app/` - Application code
- ✅ `bootstrap/` - Bootstrap files
- ✅ `config/` - Configuration files
- ✅ `database/` - Database migrations và seeds
- ✅ `lang/` - Language files
- ✅ `public/` - **QUAN TRỌNG**: Phải có `public/build/` sau khi build
- ✅ `resources/` - Resources (views, js, sass)
- ✅ `routes/` - Route files
- ✅ `storage/` - Storage directory (cấu trúc thư mục, nhưng đã xóa cache/logs/sessions)
- ✅ `vendor/` - Composer dependencies (hoặc cài lại trên server)
- ✅ `artisan` - Artisan CLI
- ✅ `composer.json` và `composer.lock`
- ✅ `package.json` và `package-lock.json`
- ✅ `vite.config.js`
- ✅ Các file config khác

### Quan trọng:
- ✅ Đảm bảo đã build frontend: `npm run build`
- ✅ Đảm bảo `public/build/` có đầy đủ files

## 🚀 BƯỚC TIẾP THEO:

1. **Build Frontend**: Chạy `npm run build` trước khi upload
2. **Upload files**: Upload tất cả files (trừ các file đã liệt kê ở trên)
3. **Tạo .env trên server**: Copy từ `.env.example` và điền thông tin production
4. **Cài đặt dependencies**: Chạy `composer install --no-dev` trên server (nếu chưa upload vendor/)
5. **Set permissions**: `chmod -R 775 storage bootstrap/cache`
6. **Clear cache**: Chạy các lệnh Laravel trên server

## 📝 GHI CHÚ:

- File `.env` phải được tạo mới trên server, KHÔNG upload từ local
- Thư mục `storage/` và `bootstrap/cache/` phải có quyền ghi (775)
- Đảm bảo PHP version >= 8.2 trên server
- Document Root phải trỏ đến `public/` folder

