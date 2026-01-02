# Tạo File .env Cho Local Development

File `.env.example` không thể tự động tạo do bảo mật. Vui lòng tạo file `.env` thủ công với nội dung sau:

## Cách Tạo File .env

1. Tạo file mới tên `.env` trong thư mục root của project
2. Copy nội dung dưới đây vào file `.env`
3. Điều chỉnh các giá trị phù hợp với môi trường local của bạn

## Nội Dung File .env

```env
APP_NAME=ONFAShop
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost/onfashop

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onfashop
DB_USERNAME=root
DB_PASSWORD=

# Broadcasting
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Pusher Configuration (Optional - for realtime features)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

# Vite/Pusher for Frontend
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# AWS S3 Configuration (Optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=

# Payment Gateways - Stripe
STRIPE_KEY=
STRIPE_SECRET=

# Payment Gateways - PayPal
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=sandbox

# Payment Gateways - Paystack
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
MERCHANT_EMAIL=

# Payment Gateways - Razorpay
RAZORPAY_KEY=
RAZORPAY_SECRET=

# Payment Gateways - Flutterwave
FLW_PUBLIC_KEY=
FLW_SECRET_KEY=
FLW_SECRET_HASH=

# Payment Gateways - Paytm
PAYTM_ENVIRONMENT=local
PAYTM_MERCHANT_ID=
PAYTM_MERCHANT_KEY=
PAYTM_MERCHANT_WEBSITE=
PAYTM_CHANNEL=
PAYTM_INDUSTRY_TYPE=

# Payment Gateways - SSLCommerz
SSLCOMMERZ_STORE_ID=
SSLCOMMERZ_STORE_PASSWORD=
SSLCOMMERZ_IS_SANDBOX=true

# Payment Gateways - Payfast
PAYFAST_MERCHANT_ID=
PAYFAST_MERCHANT_KEY=

# Payment Gateways - Authorize.net
AUTHORIZE_NET_MERCHANT_LOGIN_ID=
AUTHORIZE_NET_MERCHANT_TRANSACTION_KEY=

# Payment Gateways - Mercadopago
MERCADOPAGO_KEY=
MERCADOPAGO_ACCESS=
MERCADOPAGO_CURRENCY=

# Payment Gateways - MyFatoorah
MYFATOORAH_API_KEY=
MYFATOORAH_COUNTRY_ISO=SA

# Payment Gateways - Iyzico
IYZICO_API_KEY=
IYZICO_SECRET_KEY=

# Payment Gateways - PhonePe
PHONEPE_MERCHANT_ID=
PHONEPE_SALT_KEY=
PHONEPE_SALT_INDEX=1

# Payment Gateways - Payhere
PAYHERE_MERCHANT_ID=
PAYHERE_SECRET=

# Google OAuth (Social Login)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Facebook OAuth (Social Login)
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

# Twitter OAuth (Social Login)
TWITTER_CLIENT_ID=
TWITTER_CLIENT_SECRET=

# Recaptcha
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# Demo Mode (set to "On" to enable demo mode)
DEMO_MODE=Off

# Redis (Optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=file

# Queue
QUEUE_CONNECTION=sync
```

## Sau Khi Tạo File .env

1. Chạy lệnh để generate APP_KEY:
   ```bash
   php artisan key:generate
   ```

2. Điều chỉnh `APP_URL` nếu project của bạn ở thư mục khác:
   - Nếu project ở `http://localhost/myproject` → đổi `APP_URL=http://localhost/myproject`
   - Nếu project ở root `http://localhost` → đổi `APP_URL=http://localhost`

3. Cấu hình database:
   - Tạo database trong phpMyAdmin
   - Điền tên database vào `DB_DATABASE`
   - Điền username/password nếu MySQL có password

## Lưu Ý

- **KHÔNG** commit file `.env` vào Git
- File `.env` chứa thông tin nhạy cảm
- Luôn giữ file `.env` an toàn

