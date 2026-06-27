# 💸 Catetin

<div align="center">

<img src="resources/img/Catetin.png" alt="Catetin Logo" width="180"/>

### Cash Flow Management System for MSMEs

A web-based cash flow management application built with **Laravel 12**, designed to help small businesses record income, expenses, payment methods, and generate financial reports.

</div>

---

## ✨ Features

### 🔐 Authentication

* User Registration
* User Login & Logout
* Remember Me
* Password Validation

### 🏪 Shop Management

* Shop registration
* Shop profile management
* Automatic default setup for new shops

### 📂 Category Management

* Default income & expense categories
* Custom category CRUD
* Duplicate validation
* Prevent deletion when used by transactions

### 💳 Payment Method Management

* Default Cash payment method
* QRIS / Bank Transfer / E-Wallet support
* QR Code upload
* Active / Inactive status
* Prevent deletion when referenced

### 💰 Transaction Management

* Record Income & Expense
* Automatic transaction code generation
* Proof image upload
* Cash & Non-Cash payment support
* Transaction validation

### 📊 Dashboard

* Today's income statistics
* Transaction summary
* Cash vs Non-Cash statistics
* Weekly income chart
* Recent transactions

### 📑 Sales Report

* Date range filtering
* Category filtering
* Payment method filtering
* Income & Expense summaries
* Running balance
* PDF Export
* Excel Export

### 💸 Withdrawal

* Available balance calculation
* Monthly withdrawal quota
* Admin fee calculation
* Withdrawal history
* Balance synchronization

### 🎨 UI Features

* Responsive layout
* Light & Dark theme
* Consistent design system
* Modern dashboard interface

---

# 🛠 Tech Stack

| Technology   | Version |
| ------------ | ------- |
| PHP          | 8.3+    |
| Laravel      | 12      |
| Blade        | Latest  |
| Alpine.js    | 3       |
| Tailwind CSS | 4       |
| MySQL        | 8       |
| Vite         | Latest  |
| Pest         | Latest  |
| Laravel Pint | Latest  |

---

# 📁 Project Structure

```
app/
├── Http/
├── Models/
├── Policies/
├── Requests/
├── Services/
└── Providers/

resources/
├── views/
├── css/
├── js/
└── img/

routes/
database/
tests/
```

---

# 🚀 Installation

Clone repository

```bash
git clone https://github.com/yourusername/catetin.git
```

Masuk ke folder project

```bash
cd catetin
```

Install dependency

```bash
composer install
npm install
```

Copy environment

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Konfigurasi database pada file `.env`

```env
DB_DATABASE=catetin
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder

```bash
php artisan migrate --seed
```

Buat symbolic link untuk file upload

```bash
php artisan storage:link
```

Jalankan aplikasi

```bash
php artisan serve
npm run dev
```

---

# 📸 Screenshots

| Dashboard          | Transaction        |
| ------------------ | ------------------ |
| *(Add Screenshot)* | *(Add Screenshot)* |

| Reports            | Settings           |
| ------------------ | ------------------ |
| *(Add Screenshot)* | *(Add Screenshot)* |

---

# 🧪 Testing

Run all tests

```bash
php artisan test
```

Run Laravel Pint

```bash
vendor/bin/pint
```

---

# 📂 Main Modules

* Authentication
* Shop Management
* Category Management
* Payment Method Management
* Transaction Recording
* Dashboard & Statistics
* Sales Reports
* Withdrawal Management
* PDF & Excel Export

---

# 🔒 Security

* Authentication Middleware
* Authorization Policies
* Form Request Validation
* CSRF Protection
* Password Hashing
* File Upload Validation
* Database Transactions
* Ownership Authorization

---

# 📈 Future Improvements

* QRIS Payment Gateway Integration
* Midtrans Integration
* Xendit Integration
* GoPay / OVO / DANA API Integration
* Email Notifications
* Mobile Application
* Multi-user & Role Management
* REST API
* Real-time Dashboard

---

# 👨‍💻 Developer

**Yukira Hamiaski**

Accounting Information Systems Project

---

# 📄 License

This project is developed for educational purposes as part of an undergraduate final project (Skripsi).

Feel free to use it as a reference while respecting the original author's work.
