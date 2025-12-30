# Deployment Checklist - PHP Version

## ✅ What You NEED for PHP Deployment

**Only upload these:**

```
php/
├── api/              ✅ NEED
├── assets/           ✅ NEED
├── config/           ✅ NEED
├── includes/           ✅ NEED
├── *.php             ✅ NEED (all PHP files)
└── .htaccess         ✅ NEED
```

## ❌ What You DON'T Need

**Do NOT upload these (Node.js/React version):**

```
client/               ❌ NOT NEEDED (React frontend)
server/               ❌ NOT NEEDED (Node.js backend)
node_modules/         ❌ NOT NEEDED
package.json          ❌ NOT NEEDED
render.yaml           ❌ NOT NEEDED
verify-build.js       ❌ NOT NEEDED
```

## 📦 What to Upload to cPanel

1. **Upload ONLY the `php/` folder contents** to `public_html/`
2. **OR** upload the entire `php/` folder to `public_html/swgoh/` (or any name)

## 🗑️ Can You Delete the Node.js Version?

**Yes, you can delete it if:**
- ✅ You're only using PHP version
- ✅ You don't need the Node.js version anymore
- ✅ You want to clean up your project

**Keep it if:**
- ⚠️ You might want to switch back
- ⚠️ You want to reference the code
- ⚠️ You're deploying both versions

## 🎯 Recommended Action

**For cPanel deployment:**
1. **Upload only `php/` folder** to your cPanel
2. **Delete or ignore** `client/` and `server/` folders (they're not used)
3. **Keep them locally** if you want, but don't upload them

## 📁 Clean Project Structure

If you want to clean up, your project should look like:

```
your-project/
├── php/              ← Upload THIS to cPanel
│   ├── api/
│   ├── assets/
│   ├── config/
│   └── ...
└── (optional: keep Node.js version in separate folder)
```

**Bottom line:** For PHP deployment, you only need the `php/` folder! 🚀

