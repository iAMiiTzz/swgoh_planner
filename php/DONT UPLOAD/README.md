# SWGOH Planner - PHP Version

Complete PHP conversion of the SWGOH Planner application. Works on **any cPanel hosting** without Node.js!

## ✅ Complete Features

- ✅ User Authentication (Login/Logout)
- ✅ GAC Planner (with league-based limits)
- ✅ Journey Tracker
- ✅ Roster Planner
- ✅ Gear/Relic Planner
- ✅ Guild Planner (user list with ally codes)
- ✅ Settings (change password, username, ally codes)
- ✅ Admin Panel (user management)
- ✅ SWGOH.gg API Integration

## 📁 File Structure

```
php/
├── api/              # API endpoints (REST)
│   ├── auth.php
│   ├── gac.php
│   ├── journey.php
│   ├── roster.php
│   ├── gear.php
│   ├── guild.php
│   ├── admin.php
│   └── swgoh.php
├── assets/
│   ├── css/         # Stylesheets
│   └── js/          # JavaScript utilities
├── config/          # Database & auth config
├── includes/        # Header/footer templates
├── *.php           # Main pages
└── .htaccess       # URL rewriting
```

## 🚀 Quick Start

1. **Upload** the `php/` folder to your cPanel `public_html/`
2. **Set permissions**: Files 644, Folders 755
3. **Create admin user** (see `C PANEL_DEPLOYMENT.md`)
4. **Access**: `yourdomain.com/php/`

## 📖 Documentation

- **`C PANEL_DEPLOYMENT.md`** - Complete deployment guide
- **`PHP_CONVERSION_GUIDE.md`** - Conversion details

## 🔧 Requirements

- PHP 7.4+ (most cPanel hosts have this)
- MySQL 5.7+ (your existing database)
- mod_rewrite enabled (usually is by default)

## 🎉 No Build Process!

Unlike the Node.js version, this PHP version:
- ✅ No `npm install` needed
- ✅ No build step required
- ✅ Just upload and go!
- ✅ Works on any cPanel hosting

Perfect for your cPanel account! 🚀

