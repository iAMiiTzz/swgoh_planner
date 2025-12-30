# Deployment Guide - SWGOH Planner

This guide will help you deploy your SWGOH Planner website to a live server.

## Prerequisites

- Access to your web server (via SSH/FTP)
- Node.js installed on your server (version 14 or higher)
- MySQL database already configured (you have this)
- Domain name or server IP address

## Step 1: Prepare Your Files

### Build the Production Version

On your local machine, run:

```bash
npm run build
```

This creates an optimized production build in the `client/build` folder.

## Step 2: Upload Files to Server

### Option A: Using FTP/SFTP

1. **Connect to your server** using an FTP client (FileZilla, WinSCP, etc.)

2. **Upload these files/folders** to your server:
   - `server/` folder (entire folder)
   - `client/build/` folder (entire folder)
   - `package.json`
   - `.env` file (create this on server with your credentials)
   - `node_modules/` (or install on server - see below)

### Option B: Using SSH/SCP

```bash
# From your local machine
scp -r server/ user@your-server.com:/path/to/your/website/
scp -r client/build/ user@your-server.com:/path/to/your/website/client/
scp package.json user@your-server.com:/path/to/your/website/
```

## Step 3: Server Setup

### 1. Create `.env` File on Server

SSH into your server and create a `.env` file in the root directory:

```bash
nano .env
```

Add these contents:

```
NODE_ENV=production
PORT=5000
DB_HOST=167.99.181.177
DB_NAME=bmislandhost_plan_swgoh
DB_USER=bmislandhost_bradley
DB_PASSWORD=DragonFly$2025
JWT_SECRET=swgoh-planner-secret-key-change-this-to-a-secure-random-string-in-production
SWGOH_API_KEY=3a8ac
```

**Important:** Change `JWT_SECRET` to a long, random string for security!

### 2. Install Dependencies

On your server, navigate to your website directory and run:

```bash
npm install --production
```

This installs only production dependencies (no dev dependencies).

### 3. Install PM2 (Process Manager)

PM2 keeps your Node.js app running even if the server restarts:

```bash
npm install -g pm2
```

## Step 4: Start Your Application

### Start with PM2

**Option 1: Simple Start**
```bash
pm2 start server/index.js --name swgoh-planner
```

**Option 2: Using Ecosystem File (Recommended)**
```bash
pm2 start ecosystem.config.js
```

The ecosystem.config.js file is already configured for your app.

### Save PM2 Configuration

```bash
pm2 save
pm2 startup
```

This ensures your app restarts automatically if the server reboots.

### Check Status

```bash
pm2 status
pm2 logs swgoh-planner
```

## Step 5: Configure Web Server (If Needed)

### Option A: Direct Port Access

If your server allows direct port access, your site should be available at:
```
http://your-server-ip:5000
```

### Option B: Using Nginx (Recommended)

If you want to use a domain name and standard port 80/443, set up Nginx as a reverse proxy:

1. **Install Nginx** (if not already installed):
```bash
sudo apt-get update
sudo apt-get install nginx
```

2. **Create Nginx Configuration**:
```bash
sudo nano /etc/nginx/sites-available/swgoh-planner
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;

    location / {
        proxy_pass http://localhost:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

3. **Enable the Site**:
```bash
sudo ln -s /etc/nginx/sites-available/swgoh-planner /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Option C: Using Apache

If you're using Apache, create a virtual host configuration with mod_proxy.

## Step 6: Firewall Configuration

Make sure port 5000 (or 80/443 if using Nginx) is open:

```bash
# For Ubuntu/Debian
sudo ufw allow 5000
# Or if using Nginx
sudo ufw allow 80
sudo ufw allow 443
```

## Step 7: SSL Certificate (Optional but Recommended)

For HTTPS, use Let's Encrypt:

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

## Quick Deployment Checklist

- [ ] Built production version (`npm run build`)
- [ ] Uploaded files to server
- [ ] Created `.env` file on server with correct values
- [ ] Installed dependencies (`npm install --production`)
- [ ] Installed PM2 (`npm install -g pm2`)
- [ ] Started application with PM2
- [ ] Configured web server (Nginx/Apache) if needed
- [ ] Opened necessary firewall ports
- [ ] Tested the website

## Updating Your Site

When you make changes:

1. **On your local machine:**
   ```bash
   npm run build
   ```

2. **Upload new files:**
   - Upload `client/build/` folder (replace old one)
   - Upload any changed `server/` files

3. **On your server:**
   ```bash
   pm2 restart swgoh-planner
   ```

## Troubleshooting

### Application Won't Start
- Check PM2 logs: `pm2 logs swgoh-planner`
- Verify `.env` file exists and has correct values
- Check if port 5000 is available: `netstat -tulpn | grep 5000`

### Database Connection Issues
- Verify database credentials in `.env`
- Check if database server allows connections from your web server IP
- Test connection: `mysql -h 167.99.181.177 -u bmislandhost_bradley -p`

### 404 Errors
- Make sure `client/build` folder was uploaded correctly
- Check that `NODE_ENV=production` is set in `.env`
- Verify file permissions on server

### Port Already in Use
- Change `PORT` in `.env` to a different port (e.g., 5001, 3000)
- Update Nginx/Apache configuration if using reverse proxy

## Support

If you encounter issues:
1. Check PM2 logs: `pm2 logs swgoh-planner`
2. Check server logs
3. Verify all environment variables are set correctly
4. Test database connection separately
