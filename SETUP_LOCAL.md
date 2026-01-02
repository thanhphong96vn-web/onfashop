# 🚀 Hướng Dẫn Setup Local Development (XAMPP)

Hướng dẫn này giúp bạn chạy ONFAShop trên môi trường local với XAMPP mà không thay đổi cấu trúc source code.

## 📋 Yêu Cầu Hệ Thống

- **PHP**: >= 8.2
- **Composer**: Latest version
- **Node.js**: >= 16.x
- **npm**: >= 8.x
- **XAMPP**: Latest version (hoặc bất kỳ local server nào)
- **MySQL**: 5.7+ hoặc MariaDB 10.3+

## 🔧 Bước 1: Cài Đặt Dependencies

### 1.1. Cài đặt PHP Dependencies (Composer)

Mở terminal/PowerShell tại thư mục project và chạy:

```bash
composer install
```

**Lưu ý**: Nếu gặp lỗi về memory limit, chạy:
```bash
php -d memory_limit=-1 composer install
```

### 1.2. Cài đặt Node.js Dependencies

```bash
npm install
```

## 📝 Bước 2: Cấu Hình Environment

### 2.1. Tạo file .env

Copy file `.env.example` thành `.env`:

```bash
# Windows PowerShell
Copy-Item .env.example .env

# Hoặc Windows CMD
copy .env.example .env

# Hoặc Linux/Mac
cp .env.example .env
```

### 2.2. Cấu hình Database

Mở file `.env` và cập nhật thông tin database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onfashop
DB_USERNAME=root
DB_PASSWORD=
```

**Lưu ý**: 
- Tạo database `onfashop` trong phpMyAdmin trước
- Nếu MySQL có password, điền vào `DB_PASSWORD`

### 2.3. Cấu hình Application URL

Cập nhật `APP_URL` trong file `.env`:

```env
APP_URL=http://localhost/onfashop
```

**Lưu ý**: 
- Nếu project của bạn ở thư mục khác, thay `onfashop` bằng tên thư mục của bạn
- Ví dụ: `http://localhost/myproject`

### 2.4. Generate Application Key

Chạy lệnh sau để tạo APP_KEY:

```bash
php artisan key:generate
```

## 🗄️ Bước 3: Setup Database

### 3.1. Tạo Database

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Tạo database mới tên `onfashop` (hoặc tên bạn đã đặt trong .env)
3. Chọn collation: `utf8mb4_unicode_ci`

### 3.2. Import Database (Nếu có)

Nếu bạn có file SQL để import:

1. Chọn database vừa tạo
2. Click tab "Import"
3. Chọn file SQL và import

**Hoặc** nếu có migrations, chạy:

```bash
php artisan migrate
```

## 🔨 Bước 4: Build Frontend Assets

### 4.1. Development Mode (Hot Reload)

Chạy lệnh sau để khởi động Vite dev server:

```bash
npm run dev
```

Giữ terminal này chạy. Vite sẽ tự động reload khi bạn thay đổi code.

### 4.2. Production Build (Nếu cần)

Nếu muốn build production:

```bash
npm run build
```

## 🚀 Bước 5: Khởi Động Application

### 5.1. XAMPP Setup

1. Khởi động **Apache** và **MySQL** trong XAMPP Control Panel
2. Đảm bảo Apache chạy trên port 80
3. Đảm bảo MySQL chạy trên port 3306

### 5.2. Truy Cập Application

Mở trình duyệt và truy cập:

```
http://localhost/onfashop
```

**Lưu ý**: 
- Nếu project ở thư mục khác, thay `onfashop` bằng tên thư mục của bạn
- Nếu dùng Laravel Artisan serve, truy cập: `http://localhost:8000`

### 5.3. Laravel Artisan Serve (Tùy chọn)

Nếu không dùng XAMPP, có thể dùng Laravel built-in server:

```bash
php artisan serve
```

Sau đó truy cập: `http://localhost:8000`

## ⚙️ Bước 6: Cấu Hình Bổ Sung

### 6.1. Storage Link

Tạo symbolic link cho storage:

```bash
php artisan storage:link
```

### 6.2. Clear Cache

Clear tất cả cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 6.3. Set Permissions (Linux/Mac)

Nếu dùng Linux/Mac, set permissions:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 🔍 Bước 7: Kiểm Tra

### 7.1. Kiểm Tra Routes

Chạy lệnh để xem tất cả routes:

```bash
php artisan route:list
```

### 7.2. Kiểm Tra Database Connection

Chạy lệnh để test database:

```bash
php artisan tinker
```

Trong tinker, chạy:

```php
DB::connection()->getPdo();
```

Nếu không có lỗi, database đã kết nối thành công.

## 🐛 Troubleshooting

### Lỗi: "Class not found"

**Giải pháp**: Chạy lại:
```bash
composer dump-autoload
```

### Lỗi: "500 Internal Server Error"

**Giải pháp**: 
1. Kiểm tra file `.env` đã có `APP_KEY` chưa
2. Clear cache: `php artisan config:clear`
3. Kiểm tra file `storage/logs/laravel.log` để xem lỗi chi tiết

### Lỗi: "Vite assets not loading"

**Giải pháp**:
1. Đảm bảo đang chạy `npm run dev` (development mode)
2. Hoặc đã chạy `npm run build` (production mode)
3. Kiểm tra file `public/build/manifest.json` có tồn tại không

### Lỗi: "Database connection failed"

**Giải pháp**:
1. Kiểm tra MySQL đã khởi động chưa trong XAMPP
2. Kiểm tra thông tin database trong `.env` đúng chưa
3. Kiểm tra database đã được tạo chưa

### Lỗi: "Permission denied" (Linux/Mac)

**Giải pháp**:
```bash
chmod -R 775 storage bootstrap/cache
```

## 📚 Các Lệnh Hữu Ích

```bash
# Clear tất cả cache
php artisan optimize:clear

# Cache config (production)
php artisan config:cache

# Xem tất cả routes
php artisan route:list

# Tạo controller mới
php artisan make:controller ControllerName

# Tạo model mới
php artisan make:model ModelName

# Chạy migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Xem logs
tail -f storage/logs/laravel.log
```

## 🎯 Development Workflow

1. **Khởi động Vite dev server** (terminal 1):
   ```bash
   npm run dev
   ```

2. **Khởi động Laravel** (terminal 2 - tùy chọn):
   ```bash
   php artisan serve
   ```

3. **Truy cập**: `http://localhost/onfashop`

4. **Code và xem kết quả**: Vite sẽ tự động reload khi bạn save file

## 📝 Lưu Ý Quan Trọng

1. **Không commit file `.env`** vào Git
2. **File `.env.example`** là template, không chứa thông tin nhạy cảm
3. **Development mode**: Dùng `npm run dev` để có hot reload
4. **Production mode**: Dùng `npm run build` trước khi deploy
5. **Database**: Luôn backup database trước khi chạy migrations

## ✅ Checklist

- [ ] Đã cài đặt Composer dependencies
- [ ] Đã cài đặt NPM dependencies
- [ ] Đã tạo file `.env` từ `.env.example`
- [ ] Đã cấu hình database trong `.env`
- [ ] Đã tạo database trong phpMyAdmin
- [ ] Đã chạy `php artisan key:generate`
- [ ] Đã chạy migrations hoặc import database
- [ ] Đã chạy `php artisan storage:link`
- [ ] Đã chạy `npm run dev` hoặc `npm run build`
- [ ] Đã khởi động Apache và MySQL trong XAMPP
- [ ] Đã truy cập thành công `http://localhost/onfashop`

## 🎉 Hoàn Thành!

Nếu bạn đã hoàn thành tất cả các bước trên, ứng dụng của bạn đã sẵn sàng để phát triển!

Nếu gặp vấn đề, hãy kiểm tra:
- File `storage/logs/laravel.log` để xem lỗi chi tiết
- Console của trình duyệt (F12) để xem lỗi JavaScript
- Terminal để xem lỗi PHP/Composer

---

**Chúc bạn code vui vẻ! 🚀**

