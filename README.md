# 🏪 EPOS SAZA - Modern Point of Sale System

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-3.x-purple?style=flat-square&logo=livewire)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-blue?style=flat-square&logo=tailwindcss)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)

Sistem Point of Sale (EPOS) modern yang dibangun dengan Laravel 12, Livewire, dan TailwindCSS. Dilengkapi dengan interface yang intuitif, role-based access control, dan fitur-fitur lengkap untuk manajemen toko.

## ✨ Features

### 🎯 **Core Features**
- **Modern POS Terminal** - Interface kasir yang user-friendly
- **Product Management** - Manajemen produk dengan kategori dan stok
- **Inventory Control** - Tracking stok real-time dengan alert
- **Multi-Payment Support** - Cash, QRIS, RFID, Card payment
- **Role-Based Access** - Admin, Manager, dan Kasir dengan permission berbeda
- **Real-time Dashboard** - Analytics dan statistik penjualan
- **Transaction History** - Riwayat transaksi lengkap

### 🎨 **UI/UX Features**
- **Glassmorphism Design** - Modern transparent effects
- **Responsive Layout** - Mobile-friendly interface
- **Dark/Light Theme** - Theme switching capability
- **Collapsible Sidebar** - Space-efficient navigation
- **Real-time Notifications** - Live alerts dan status updates
- **Smooth Animations** - Micro-interactions untuk better UX

### 🔐 **Security & Access Control**
- **Multi-Role System** - 3-tier role management
- **Secure Authentication** - Laravel Breeze integration
- **Session Management** - Proper logout dan security
- **CSRF Protection** - Form security

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite/MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/EPOS_SAZA.git
   cd EPOS_SAZA
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=AdminUserSeeder
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the server**
   ```bash
   php artisan serve
   ```

Visit `http://127.0.0.1:8000` to access the application.

## 👤 Demo Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Admin** | admin@epos.com | password123 | Full system access |
| **Manager** | manager@epos.com | password123 | Sales, inventory, reports |
| **Cashier** | kasir@epos.com | password123 | POS terminal, personal reports |
| **Cashier 2** | kasir2@epos.com | password123 | POS terminal, personal reports |

## 📱 Application Structure

### **Dashboard** (`/dashboard`)
- Real-time sales statistics
- Revenue tracking
- Low stock alerts
- Recent transactions
- Quick action buttons

### **POS Terminal** (`/pos`)
- Product selection grid
- Shopping cart management
- Customer selection
- Multiple payment methods
- Receipt generation
- Return/refund processing

### **Product Management** (`/products`)
- Product CRUD operations
- Category management
- Stock level monitoring
- Price management
- Barcode generation
- Bulk operations

### **Role-Based Navigation**
- **Admin**: Full access to all features
- **Manager**: Limited to operations (no user management)
- **Cashier**: POS-focused interface with personal reports

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x
- **Frontend**: Livewire 3.x + Alpine.js
- **Styling**: TailwindCSS 3.x
- **Database**: SQLite (default) / MySQL
- **Icons**: Font Awesome 6
- **Fonts**: Inter
- **Build Tool**: Vite

## 🎯 Payment Methods Supported

| Method | Status | Description |
|--------|--------|-------------|
| 💰 **Cash** | ✅ Ready | Traditional cash payment |
| 📱 **QRIS** | ✅ Ready | QR Code Indonesian Standard |
| 💳 **RFID/NFC** | ✅ Ready | Contactless card payment |
| 💳 **Card Terminal** | 🔧 Integration Ready | Debit/Credit card support |
| 🏦 **Bank Transfer** | 🔧 Integration Ready | Online banking |
| 💸 **E-Wallet** | 🔧 Integration Ready | Digital wallet support |

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 🙏 Acknowledgments

- Laravel Team for the amazing framework
- Livewire Team for reactive components
- TailwindCSS for utility-first CSS
- Font Awesome for beautiful icons

---

**Built with ❤️ using Laravel 12 + Livewire + TailwindCSS**
