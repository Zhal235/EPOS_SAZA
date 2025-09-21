# EPOS SAZA - Login Credentials

## 🔐 Demo User Accounts

Gunakan kredensial berikut untuk testing aplikasi EPOS SAZA:

### Admin Account
- **Email:** admin@epos.com
- **Password:** password123
- **Role:** Administrator
- **Access:** Full system access

### Manager Account
- **Email:** manager@epos.com
- **Password:** password123
- **Role:** Manager
- **Access:** Sales, inventory, reports (no user management)

### Kasir Account 1
- **Email:** kasir@epos.com
- **Password:** password123
- **Role:** Cashier
- **Access:** POS, personal sales reports

### Kasir Account 2
- **Email:** kasir2@epos.com
- **Password:** password123
- **Role:** Cashier
- **Access:** POS, personal sales reports

## 🌐 Akses Aplikasi

1. **Login Page:** http://127.0.0.1:8000/login
2. **Dashboard:** http://127.0.0.1:8000/dashboard (setelah login)
3. **POS Terminal:** http://127.0.0.1:8000/pos
4. **Products:** http://127.0.0.1:8000/products (Admin & Manager only)

## 🚀 Cara Testing Role-Based Navigation

1. Login sebagai **Admin** untuk melihat semua menu
2. Login sebagai **Manager** untuk melihat menu terbatas (tanpa user management)
3. Login sebagai **Kasir** untuk melihat menu kasir saja (POS + personal reports)
4. Perhatikan perbedaan sidebar menu untuk setiap role

## ✨ Fitur Role-Based

### 👑 Admin Features
- ✅ Full dashboard access
- ✅ POS Terminal
- ✅ Complete inventory management
- ✅ All reports & analytics
- ✅ Staff management
- ✅ System settings
- ✅ Customer management

### 👔 Manager Features
- ✅ Dashboard access
- ✅ POS Terminal
- ✅ Inventory management
- ✅ Sales & financial reports
- ✅ Customer management
- ❌ Staff management
- ❌ System settings

### 💰 Cashier Features
- ✅ Dashboard (limited view)
- ✅ POS Terminal
- ✅ Personal sales reports
- ✅ My shift information
- ✅ Notifications
- ❌ Inventory management
- ❌ Financial reports
- ❌ Staff management
- ❌ System settings

## 🎨 UI/UX Features

- **Dynamic Sidebar** - Menu berubah sesuai role user
- **Role Indicators** - Icon dan badge untuk menunjukkan role
- **Collapsible Sidebar** - Dapat diperkecil/diperbesar
- **Glassmorphism Design** - Modern transparent effects
- **Responsive Layout** - Mobile-friendly design
- **Real-time Notifications** - Badge dan alert system

---

**Powered by Laravel 12 + Livewire + Tailwind CSS**