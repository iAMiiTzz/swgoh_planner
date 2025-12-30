# cPanel Deployment Guide - PHP Version

## ✅ What You Have

A complete PHP application that works on **any cPanel hosting** without Node.js!

## 🚀 Quick Deployment Steps

### Step 1: Upload Files

1. **Connect to your cPanel** (File Manager or FTP)
2. **Navigate to `public_html/`** (or your domain's root directory)
3. **Upload the entire `php/` folder** contents to `public_html/`

**OR** if you want it in a subfolder:
- Upload the `php/` folder to `public_html/swgoh/` (or any name you want)
- Access at: `yourdomain.com/swgoh/`

### Step 2: Set Permissions

In cPanel File Manager:
- **PHP files**: 644
- **Folders**: 755
- **`php/config/` folder**: 755 (should be readable)

### Step 3: Configure Database

The database connection is already configured in `php/config/database.php`:
- Host: `167.99.181.177`
- Database: `bmislandhost_plan_swgoh`
- User: `bmislandhost_bradley`
- Password: `DragonFly$2025`

**No changes needed** - it's already set up!

### Step 4: Access Your Site

1. **Visit:** `yourdomain.com/php/` (or `yourdomain.com/swgoh/` if you renamed it)
2. **You'll be redirected to login**
3. **Create an admin user** (see below)

## 👤 Creating an Admin User

You need to create the first admin user. You can do this by:

### Option 1: Direct Database Insert (Recommended)

1. Go to cPanel → **phpMyAdmin**
2. Select database: `bmislandhost_plan_swgoh`
3. Click **SQL** tab
4. Run this query (replace with your desired username/password):

```sql
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin');
```

To generate a password hash, use this PHP code in a temporary file:

```php
<?php
echo password_hash('your-password-here', PASSWORD_DEFAULT);
?>
```

### Option 2: Create via PHP Script

Create a file `php/create-admin.php`:

```php
<?php
require_once 'config/database.php';

$username = 'admin'; // Change this
$password = 'your-password'; // Change this
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$conn = getDB();
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
$stmt->bind_param("ss", $username, $hashedPassword);
$stmt->execute();

echo "Admin user created! Username: $username, Password: $password";
echo "<br><a href='login.php'>Go to Login</a>";
?>
```

1. Upload this file
2. Visit `yourdomain.com/php/create-admin.php` in your browser
3. **Delete the file immediately after** for security!

## 📁 File Structure

```
public_html/
├── php/
│   ├── api/              # API endpoints
│   ├── assets/           # CSS, JS, images
│   ├── config/           # Database & auth config
│   ├── includes/         # Header/footer templates
│   ├── *.php            # Main pages
│   └── .htaccess        # URL rewriting
```

## ✅ What Works

- ✅ Login/Logout
- ✅ GAC Planner
- ✅ Journey Tracker
- ✅ Roster Planner
- ✅ Gear/Relic Planner
- ✅ Guild Planner (user list)
- ✅ Settings (change password, username, ally codes)
- ✅ Admin Panel (user management)
- ✅ SWGOH.gg API integration

## 🔒 Security Notes

1. **Delete `create-admin.php`** after creating admin user
2. **Keep `config/database.php` secure** - it contains credentials
3. **Use HTTPS** if possible (most cPanel hosts provide free SSL)
4. **Regular backups** of your database

## 🐛 Troubleshooting

### "Database connection failed"
- Check database credentials in `php/config/database.php`
- Verify database exists in cPanel
- Check if MySQL is enabled

### "404 Not Found"
- Make sure `.htaccess` file is uploaded
- Check file permissions (644 for files, 755 for folders)
- Verify mod_rewrite is enabled (contact hosting if needed)

### "Permission denied"
- Set folder permissions to 755
- Set file permissions to 644
- Check `config/` folder is readable

### "Session not working"
- Check PHP sessions are enabled (usually are by default)
- Clear browser cookies
- Check `php.ini` session settings (contact hosting if needed)

## 🎉 That's It!

Your PHP application is now live on cPanel! No Node.js, no build process, no complicated setup.

Just upload and go! 🚀

