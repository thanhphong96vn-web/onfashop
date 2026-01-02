# HƯỚNG DẪN SỬA LỖI LOCALHOST TRÊN SERVER

## Vấn đề
Assets vẫn load từ `http://localhost/onfashop/public/build/...` thay vì relative paths.

## Các bước sửa lỗi trên server

### 1. XÓA FILE HOT (QUAN TRỌNG NHẤT)
File `hot` khiến Vite nghĩ đang ở dev mode và load từ localhost.

```bash
cd public_html  # hoặc public_html/onfashop
rm -f public/hot
rm -f storage/vite.hot
rm -f public/build/hot
rm -f public/.vite/hot
```

### 2. KIỂM TRA VÀ SỬA .ENV
Đảm bảo `.env` trên server có:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://onfashop.site
ASSET_URL=

# QUAN TRỌNG: VITE_PUSHER phải hardcode, không dùng biến
VITE_PUSHER_APP_KEY=3f1856db39ffcf980bca
VITE_PUSHER_APP_CLUSTER=ap1
```

**LƯU Ý**: 
- `ASSET_URL` phải để TRỐNG hoặc không có dòng này!
- `VITE_PUSHER_APP_KEY` và `VITE_PUSHER_APP_CLUSTER` phải hardcode giá trị, KHÔNG dùng `${PUSHER_APP_KEY}` vì Laravel không expand biến trong .env!

**QUAN TRỌNG**: `ASSET_URL` phải để TRỐNG hoặc không có dòng này!

### 3. CLEAR TẤT CẢ CACHE
```bash
cd public_html  # hoặc public_html/onfashop
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### 4. REBUILD CONFIG CACHE (NẾU CẦN)
```bash
php artisan config:cache
php artisan view:cache
```

### 5. KIỂM TRA FILE ĐÃ UPLOAD
Đảm bảo đã upload:
- ✅ `app/Providers/AppServiceProvider.php` (file mới với override UrlGenerator)
- ✅ `.htaccess` (file mới với MIME types và rewrite rules)
- ✅ `public/build/` (toàn bộ thư mục build mới)

### 6. KIỂM TRA MANIFEST.JSON
Mở file `public/build/manifest.json` và kiểm tra xem paths có localhost không:
```json
{
  "resources/js/app.js": {
    "file": "assets/app-xxxxx.js"
  }
}
```

Paths trong manifest.json phải là relative (không có `http://localhost`).

### 7. KIỂM TRA HTML SOURCE
Mở website, view page source, và kiểm tra thẻ `<script>` và `<link>`:
- ❌ SAI: `<script src="http://localhost/onfashop/public/build/...">`
- ✅ ĐÚNG: `<script src="/public/build/assets/...">`

### 8. RESTART PHP-FPM (NẾU CẦN)
Nếu vẫn không được, restart PHP-FPM:
```bash
# Trong cPanel hoặc terminal
service php-fpm restart
# hoặc
/scripts/restartsrv_php-fpm
```

### 9. KIỂM TRA PERMISSIONS
```bash
chmod 755 public/build
chmod 644 public/build/**/*
```

## Nếu vẫn còn lỗi

### Kiểm tra xem có file hot không:
```bash
find . -name "hot" -type f
```

### Kiểm tra config cache:
```bash
cat bootstrap/cache/config.php | grep APP_URL
```

Nếu thấy `localhost`, chạy:
```bash
php artisan config:clear
php artisan config:cache
```

### Kiểm tra view cache:
```bash
php artisan view:clear
```

## File đã sửa
1. `app/Providers/AppServiceProvider.php` - Override UrlGenerator và Vite asset paths
2. `.htaccess` - MIME types và rewrite rules
3. `resources/js/plugins/i18n.js` - Legacy mode cho i18n
4. `vite.config.js` - base: '/'

## Sau khi sửa xong
1. Hard refresh browser (Ctrl+Shift+R)
2. Kiểm tra Console không còn CORS errors
3. Kiểm tra Network tab, tất cả assets load từ domain chính, không có localhost

