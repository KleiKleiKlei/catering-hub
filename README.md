# CateringHub - Catering Web Application

A simple, beginner-friendly catering management system built with HTML, CSS, JavaScript, and PHP.

## Table of Contents
1. [Overview](#overview)
2. [System Requirements](#system-requirements)
3. [Installation & Deployment](#installation--deployment)
4. [Database Setup](#database-setup)
5. [Configuration](#configuration)
6. [Project Structure](#project-structure)
7. [Features](#features)
8. [How to Use](#how-to-use)
9. [API Documentation](#api-documentation)
10. [Troubleshooting](#troubleshooting)

---

## Overview

CateringHub is a web-based catering management platform with two user roles:

- **Admin**: Can register, manage weekly menu calendar, add/edit food items, and manage user accounts
- **User**: Can register, view weekly menu, place orders, and manage their profile

**Tech Stack:**
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP 8.2+
- Database: MySQL 5.7+
- Server: Apache 2.4+ (via XAMPP)

---

## System Requirements

### For Deployment:
- **Windows 10/11** (or any OS supporting XAMPP)
- **XAMPP 8.0+** (includes Apache, MySQL, PHP)
- **Modern Web Browser** (Chrome, Firefox, Edge)
- **Disk Space**: ~100MB

### For Development:
- Code editor (VS Code recommended)
- Git (optional)

---

## Installation & Deployment

### Step 1: Download XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install to default location: `C:\xampp`
3. Launch XAMPP Control Panel

### Step 2: Clone/Copy Project Files
```
Option A - Using Terminal:
  1. Open PowerShell
  2. Run: git clone <repo-url> e:\cater
  
Option B - Manual Copy:
  1. Copy project folder to e:\cater
  2. Or any location on your drive
```

### Step 3: Deploy to XAMPP
```powershell
# Copy all project files to XAMPP htdocs
xcopy e:\cater\* C:\xampp\htdocs\cater\ /E /Y

# Or use PowerShell
Copy-Item -Path "e:\cater\*" -Destination "C:\xampp\htdocs\cater\" -Recurse -Force
```

### Step 4: Start Services
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait for both to show green (✓)

### Step 5: Access the App
Open browser and navigate to:
```
http://localhost/cater/
```

---

## Database Setup

### Step 1: Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click **New** in left sidebar
3. Database name: `catering_app`
4. Collation: `utf8mb4_unicode_ci`
5. Click **Create**

### Step 2: Import Database Schema
```sql
-- Copy contents of backend/database.sql and paste into SQL tab
-- Or use Import feature in phpMyAdmin

1. In phpMyAdmin, select database `catering_app`
2. Click **Import** tab
3. Click **Choose File** → select `backend/database.sql`
4. Click **Import**
```

### Step 3: Verify Tables
After import, you should see these tables:
- `users` - Regular user accounts
- `admin` - Admin accounts
- `food_menu` - Food items and dates
- `orders` - Customer orders
- `order_items` - Individual items in orders

---

## Configuration

### Database Connection
File: `backend/config.php`

```php
$host = 'localhost';
$user = 'root';
$password = '';  // Default XAMPP password is empty
$database = 'catering_app';
```

**Note:** Change password if you set one for MySQL root user.

### API Endpoints
All API files are in the root directory:
- `api-admin.php` - Admin registration/login
- `api-users.php` - User management
- `api-menu.php` - Menu operations

---

## Project Structure

```
cater/
├── index.html                 # Home page
├── login.html                 # Login page
├── register.html              # User registration
├── register-admin.html        # Admin registration
├── admin-dashboard.html       # Admin dashboard
├── admin-users.html           # Admin: manage users
├── admin-menu.html            # Admin: menu calendar
├── user-calendar.html         # User: view menu
├── user-profile.html          # User: profile page
│
├── css/
│   └── style.css              # All styling (animations, responsive)
│
├── js/
│   ├── auth.js                # Authentication & session management
│   ├── main.js                # Shared functions (login, logout, utilities)
│   ├── admin.js               # Admin-specific functions
│   ├── admin-register.js      # Admin registration handler
│   └── user.js                # User-specific functions
│
├── backend/
│   ├── config.php             # Database connection
│   ├── database.sql           # Database schema
│   ├── UserSession.php        # Session management class
│   └── uploads/               # Food item images stored here
│
├── api-admin.php              # Admin API endpoints
├── api-users.php              # User API endpoints
├── api-menu.php               # Menu API endpoints
│
└── README.md                  # This file
```

---

## Features

### Admin Features
✅ **Admin Registration** - Create admin account with email/password
✅ **Admin Login** - Secure login with session management
✅ **Dashboard** - View user statistics
✅ **Menu Calendar** - Weekly calendar view for adding food items
✅ **Food Management** - Add/edit food items with images
✅ **User Management** - View and manage user accounts (enable/disable)
✅ **Logout** - Secure session termination

### User Features
✅ **User Registration** - Create account with name/email/password
✅ **User Login** - Secure login with session management
✅ **View Menu** - See weekly menu calendar
✅ **Place Orders** - Order food items (frontend ready)
✅ **Profile Management** - View/edit profile
✅ **Logout** - Secure session termination

### Security Features
✅ **Page Protection** - Non-logged-in users redirected to login
✅ **Session Management** - Browser SessionStorage for user tracking
✅ **Password Hashing** - bcrypt for secure password storage
✅ **Input Validation** - Server-side validation on API endpoints

---

## How to Use

### First Time Setup (5-Step Process)

#### Step 1: Register Admin
1. Go to `http://localhost/cater/register-admin.html`
2. Fill in:
   - Full Name: "John Admin"
   - Email: "admin@test.com"
   - Password: "admin123"
   - Confirm Password: "admin123"
3. Click **Register Admin**
4. Success message appears

#### Step 2: Login as Admin
1. Go to `http://localhost/cater/login.html`
2. Select Role: **Admin** (dropdown)
3. Email: "admin@test.com"
4. Password: "admin123"
5. Click **Login**
6. Redirected to Admin Dashboard

#### Step 3: Add Food Items to Weekly Menu
1. Click **Menu Calendar** in navbar
2. Click a day in the calendar
3. Fill in food details:
   - Food Name: "Pasta Carbonara"
   - Description: "Creamy pasta dish"
   - Image: (upload jpg/png)
4. Click **Save Food Item**
5. Item added to that day

#### Step 4: Register User Account
1. Go to `http://localhost/cater/register.html`
2. Fill in:
   - Full Name: "Jane User"
   - Email: "user@test.com"
   - Phone: "123-456-7890"
   - Password: "user123"
   - Confirm Password: "user123"
3. Click **Register User**
4. Success message

#### Step 5: Login as User
1. Go to `http://localhost/cater/login.html`
2. Select Role: **User** (dropdown)
3. Email: "user@test.com"
4. Password: "user123"
5. Click **Login**
6. See user calendar with available menu items

---

## API Documentation

### Admin API (`api-admin.php`)

#### Register Admin
```
POST /api-admin.php?action=register
Content-Type: application/x-www-form-urlencoded

Parameters:
- name (required): Admin's full name
- email (required): Admin's email (unique)
- password (required): Password (min 6 chars)

Response:
{
  "status": "success",
  "message": "Admin registered successfully",
  "admin_id": 1
}
```

#### Admin Login
```
POST /api-admin.php?action=login
Content-Type: application/x-www-form-urlencoded

Parameters:
- email (required): Admin email
- password (required): Password

Response:
{
  "status": "success",
  "message": "Login successful",
  "admin_id": 1,
  "name": "John Admin",
  "email": "admin@test.com"
}
```

### Users API (`api-users.php`)

#### Register User
```
POST /api-users.php?action=register
Content-Type: application/x-www-form-urlencoded

Parameters:
- name (required): User's full name
- email (required): User email (unique)
- phone (required): Contact number
- password (required): Password (min 6 chars)

Response:
{
  "status": "success",
  "message": "Registration successful",
  "user_id": 1
}
```

#### User Login
```
POST /api-users.php?action=login
Content-Type: application/x-www-form-urlencoded

Parameters:
- email (required): User email
- password (required): Password

Response:
{
  "status": "success",
  "message": "Login successful",
  "user_id": 1,
  "name": "Jane User",
  "email": "user@test.com"
}
```

### Menu API (`api-menu.php`)

#### Add Food Item
```
POST /api-menu.php?action=add_food
Content-Type: multipart/form-data

Parameters:
- menu_date (required): Date (YYYY-MM-DD)
- food_name (required): Food name
- food_description (optional): Description
- food_image (optional): Image file (jpg/png)

Response:
{
  "status": "success",
  "message": "Food item added successfully",
  "food_id": 1
}
```

#### Get Weekly Menu
```
GET /api-menu.php?action=get_weekly_menu&date=2025-12-24

Parameters:
- date (required): Start date of week (YYYY-MM-DD)

Response:
{
  "status": "success",
  "data": [
    {
      "food_id": 1,
      "food_name": "Pasta Carbonara",
      "food_description": "Creamy pasta",
      "menu_date": "2025-12-24",
      "food_image": "/uploads/image.jpg"
    }
  ]
}
```

---

## Troubleshooting

### Issue: "Cannot connect to database"
**Solution:**
1. Verify MySQL is running (green in XAMPP Control Panel)
2. Check `backend/config.php` credentials
3. In phpMyAdmin, confirm `catering_app` database exists
4. Restart MySQL service

### Issue: "404 Not Found" when accessing pages
**Solution:**
1. Verify files are in `C:\xampp\htdocs\cater\`
2. Access via `http://localhost/cater/` (with trailing slash)
3. Check file names match URLs exactly (case-sensitive on Linux)

### Issue: "ReferenceError: getCurrentWeek is not defined"
**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete in Chrome)
2. Hard refresh page (Ctrl+Shift+R)
3. Check script loading order in HTML (auth.js → main.js → admin.js)

### Issue: "Logout button doesn't work"
**Solution:**
1. Verify `main.js` is loaded before `admin.js`
2. Check browser console for JavaScript errors
3. Restart Apache

### Issue: Image upload fails for food items
**Solution:**
1. Check `backend/uploads/` folder exists and is writable
2. Verify `api-menu.php` has correct file path
3. Check file size is under 5MB
4. Use jpg/png format only

### Issue: Login redirects to login page immediately
**Solution:**
1. SessionStorage might be blocked
2. Clear browser data
3. Check if cookies/storage is enabled in browser settings
4. Try incognito/private browsing mode

### Issue: Apache won't start
**Solution:**
1. Check if port 80 is in use: `netstat -ano | findstr :80`
2. Stop process using port 80
3. Or change Apache port in `C:\xampp\apache\conf\httpd.conf`
4. Restart Apache

---

## Testing Checklist

- [ ] Admin registration works
- [ ] Admin login works
- [ ] Admin can add food items
- [ ] Admin can view dashboard stats
- [ ] User registration works
- [ ] User login works
- [ ] User can view weekly menu
- [ ] Logout works on all pages
- [ ] Page protection redirects to login
- [ ] Images upload correctly
- [ ] No console JavaScript errors

---

## Common Passwords for Testing

```
Admin Account:
  Email: admin@test.com
  Password: admin123

Test User Account:
  Email: user@test.com
  Password: user123
```

---

## Future Enhancements

- [ ] Order payment integration
- [ ] Email notifications
- [ ] Ratings & reviews
- [ ] SMS alerts
- [ ] Mobile app
- [ ] Inventory management
- [ ] Delivery tracking
- [ ] Advanced reporting

---

## Support & Contact

For issues or questions:
1. Check the Troubleshooting section above
2. Review console errors (F12 in browser)
3. Check XAMPP logs in `C:\xampp\logs\`

---

## License

This project is provided as-is for educational purposes.

---

**Last Updated:** December 24, 2025
**Version:** 1.0
**Made By:** Klei
