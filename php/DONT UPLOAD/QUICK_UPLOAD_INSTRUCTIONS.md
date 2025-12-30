# Quick Upload Instructions

## ✅ YES - Upload Contents to public_html/

If you want your site at `yourdomain.com` (main domain):

### What to Do:

1. **Open the `php/` folder** on your computer
2. **Select ALL files and folders inside it:**
   - `api/` folder
   - `assets/` folder
   - `config/` folder
   - `includes/` folder
   - All `.php` files (admin.php, gac.php, login.php, etc.)
   - `.htaccess` file
3. **Upload them directly to `public_html/`** in cPanel
4. **DO NOT upload the `php/` folder itself** - just its contents

### Result:

```
public_html/
├── api/              ← From php/api/
├── assets/           ← From php/assets/
├── config/           ← From php/config/
├── includes/         ← From php/includes/
├── admin.php         ← From php/
├── gac.php           ← From php/
├── login.php         ← From php/
├── index.php         ← From php/
├── .htaccess         ← From php/
└── ... (all other PHP files)
```

### Your Site Will Be At:
- `yourdomain.com` ✅
- `yourdomain.com/login.php` ✅
- `yourdomain.com/homepage.php` ✅

---

## Alternative: Keep in Subfolder

If you want it at `yourdomain.com/php/`:

1. **Upload the entire `php/` folder** to `public_html/`
2. Your site will be at `yourdomain.com/php/`

---

## 🎯 Recommended: Upload Contents to Root

**Most people want:** `yourdomain.com` (not `yourdomain.com/php/`)

So yes - take everything OUT of `php/` folder and put it in `public_html/`! ✅

