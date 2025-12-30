# Upload Guide - What to Upload to cPanel

## 📦 What to Upload

**Upload ONLY the `php/` folder and ALL its contents.**

## 📁 Folder Structure to Upload

```
php/
├── api/                    ✅ UPLOAD
│   ├── admin.php
│   ├── auth.php
│   ├── gac.php
│   ├── gear.php
│   ├── guild.php
│   ├── journey.php
│   ├── roster.php
│   └── swgoh.php
├── assets/                 ✅ UPLOAD
│   ├── css/
│   └── js/
├── config/                 ✅ UPLOAD
│   ├── auth.php
│   └── database.php
├── includes/               ✅ UPLOAD
│   ├── footer.php
│   └── header.php
├── .htaccess               ✅ UPLOAD (important!)
├── admin.php               ✅ UPLOAD
├── gac.php                 ✅ UPLOAD
├── gear.php                ✅ UPLOAD
├── guild.php               ✅ UPLOAD
├── homepage.php            ✅ UPLOAD
├── index.php               ✅ UPLOAD
├── journey.php             ✅ UPLOAD
├── login.php               ✅ UPLOAD
├── roster.php              ✅ UPLOAD
└── settings.php            ✅ UPLOAD
```

## 🚀 Step-by-Step Upload Instructions

### Option 1: Upload Entire `php/` Folder (Recommended)

1. **Open cPanel File Manager** (or use FTP client like FileZilla)

2. **Navigate to `public_html/`** (your website's root directory)

3. **Upload the entire `php/` folder:**
   - Select the `php/` folder from your computer
   - Upload it to `public_html/`
   - Your site will be at: `yourdomain.com/php/`

### Option 2: Upload Contents to Root (For Main Domain)

If you want the site at `yourdomain.com` (not in a subfolder):

1. **Navigate to `public_html/`**

2. **Upload ALL contents from inside the `php/` folder:**
   - Upload `api/` folder
   - Upload `assets/` folder
   - Upload `config/` folder
   - Upload `includes/` folder
   - Upload all `.php` files (admin.php, gac.php, etc.)
   - Upload `.htaccess` file
   - **DO NOT upload the `php/` folder itself** - just its contents

3. **Your site will be at:** `yourdomain.com`

## 📋 Upload Checklist

Before uploading, make sure you have:
- ✅ All files from `php/` folder
- ✅ `.htaccess` file (important for routing!)
- ✅ All subfolders (`api/`, `assets/`, `config/`, `includes/`)
- ✅ All PHP files

## ⚙️ After Upload - Set Permissions

In cPanel File Manager:
- **PHP files** (.php): `644`
- **Folders**: `755`
- **`.htaccess`**: `644`

## 🎯 Quick Upload Summary

**Easiest method:**
1. Zip the `php/` folder on your computer
2. Upload the zip to `public_html/` in cPanel
3. Extract it in cPanel File Manager
4. Done! Access at `yourdomain.com/php/`

## ❌ Do NOT Upload

- ❌ `.gitignore`
- ❌ `README.md` (root level)
- ❌ Any `.env` files
- ❌ Any Node.js files (if you still have them)
- ❌ Any documentation files (optional, but not needed)

## ✅ What You'll Have After Upload

```
public_html/
└── php/              ← Your entire application
    ├── api/
    ├── assets/
    ├── config/
    ├── includes/
    └── *.php
```

**That's it!** Just the `php/` folder and everything inside it! 🎉

