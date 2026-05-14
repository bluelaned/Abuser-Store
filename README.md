<h1 align="center">
  <br>
  🛒 Abuser Store
  <br>
</h1>

<p align="center">
  A modern e-commerce platform built with Laravel — featuring multi-payment gateway support, Discord OAuth, membership tiers, promo codes, and a powerful admin dashboard.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" />
</p>

---

## ✨ Features

- 🔐 **Authentication** — Email/password login + Discord OAuth integration
- 🛍️ **Product Management** — Products with multiple variants, images, stock tracking, and USD/IDR pricing
- 💳 **Multi-Payment Gateway** — Midtrans (IDR) & Stripe (USD) payment integration
- 🎟️ **Promo & Voucher System** — Discount codes with usage limits, expiry dates, and advanced rules
- 👑 **Membership Tier System** — Bronze → Silver → Gold → Platinum → Diamond tiers with animated profile frames
- ⭐ **Reviews** — User product reviews and ratings
- 🗂️ **Admin Dashboard** — Full CRUD for products, transactions, users, promos, and vouchers
- 👤 **User Profile** — Customizable profile with bio, banner, and equipped tier frame
- 🌐 **Multi-language Support** — Dynamic language switching (EN/ID)
- 📄 **Invoice Generation** — Auto-generated invoices for completed transactions

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 10 (PHP 8.1+) |
| Frontend | Blade Templates + Vite |
| Database | MySQL |
| Auth | Laravel Sanctum + Laravel Socialite |
| Payment | Midtrans PHP SDK + Stripe PHP SDK |
| OAuth | Discord (via SocialiteProviders) |
| HTTP Client | Guzzle 7 |

---

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL / MariaDB
- Node.js & NPM
- XAMPP / Laragon / any local server

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/bluelaned/Abuser-Store.git
cd Abuser-Store
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env` and fill in your configuration (see [Environment Variables](#-environment-variables) below).

### 5. Run database migrations

```bash
php artisan migrate
```

### 6. Set up storage symlink

```bash
php artisan storage:link
```

### 7. Build frontend assets

```bash
npm run dev
```

### 8. Start the server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## ⚙️ Environment Variables

Copy `.env.example` to `.env` and configure the following:

```env
# App
APP_NAME="Abuser Store"
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abuser_store
DB_USERNAME=root
DB_PASSWORD=

# Discord OAuth
DISCORD_CLIENT_ID=your_discord_client_id
DISCORD_CLIENT_SECRET=your_discord_client_secret
DISCORD_REDIRECT_URI=http://localhost:8000/auth/discord/callback

# Midtrans (IDR Payments)
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false

# Stripe (USD Payments)
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```

---

## 👑 Setting Admin Account

After registering an account, you can promote it to admin by running:

```bash
php set_admin.php
```

Or manually update the `users` table:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

---

## 📁 Project Structure

```
abuser-store/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # Login & Register
│   │   ├── DiscordAuthController.php   # Discord OAuth
│   │   ├── ProductController.php       # Product CRUD
│   │   ├── PaymentController.php       # Midtrans & Stripe
│   │   ├── TransactionController.php   # Order management
│   │   ├── ProfileController.php       # User profile
│   │   ├── PromoController.php         # Promo codes
│   │   ├── VoucherController.php       # Vouchers
│   │   ├── ReviewController.php        # Reviews
│   │   └── AdminUserController.php     # Admin: user management
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Variant.php
│       ├── Transaction.php
│       ├── Promo.php
│       ├── VoucherCode.php
│       ├── Review.php
│       └── ProductImage.php
├── resources/views/
│   ├── admin/                          # Admin dashboard views
│   ├── auth/                           # Login & register
│   ├── profile/                        # User profile
│   ├── payment/                        # Payment pages
│   ├── checkout.blade.php
│   ├── index.blade.php                 # Storefront
│   └── reviews.blade.php
├── database/migrations/                # 33 migrations
├── public/
│   └── storage/                        # Uploaded images
└── routes/
    └── web.php
```

---

## 💳 Payment Flow

```
User selects product & variant
        ↓
    Checkout page
        ↓
  Select currency (IDR / USD)
        ↓
  ┌─────────────────┐
  │  IDR → Midtrans │  (Credit Card, QRIS, Bank Transfer, E-wallet)
  │  USD → Stripe   │  (Credit Card)
  └─────────────────┘
        ↓
  Payment Callback / Webhook
        ↓
  Transaction marked as PAID
        ↓
  Voucher/digital goods delivered
```

---

## 👤 Membership Tiers

| Tier | Badge | Benefit |
|------|-------|---------|
| 🥉 Bronze | Default | Basic access |
| 🥈 Silver | Silver frame | Silver perks |
| 🥇 Gold | Gold animated frame | Gold perks |
| 💎 Platinum | Platinum crystal frame | Platinum perks |
| 💠 Diamond | Diamond electric frame | Premium perks |

Membership tiers are calculated based on total spending. Users can equip animated profile frames based on their tier.

---

## 🔒 Security Notes

- Never commit your `.env` file — it is already in `.gitignore`
- Rotate your GitHub Personal Access Token after use
- Use environment variables for all API keys and secrets
- Set `APP_DEBUG=false` and `APP_ENV=production` in production

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 👤 Author

**bluelaned** — [github.com/bluelaned](https://github.com/bluelaned)

---

<p align="center">Made with ❤️ using Laravel</p>
