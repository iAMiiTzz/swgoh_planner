# Fix for Render.com Deployment Error

## The Problem
Error: `/opt/render/project/src/client/build/index.html` not found

This happens because Render.com needs to **build the React frontend** before starting the server.

## Solution

I've created a `render.yaml` file that will automatically configure Render.com correctly.

### Option 1: Use render.yaml (Easiest)

1. **The `render.yaml` file is already in your repo** - just push it to GitHub
2. **In Render.com:**
   - Go to your service settings
   - Click "Apply Configuration from render.yaml"
   - Or delete and recreate the service - Render will auto-detect the file

### Option 2: Manual Configuration in Render.com

If you prefer to set it up manually:

1. **Go to your Render.com service settings**

2. **Build Command:**
   ```
   npm install && cd client && npm install && npm run build
   ```

3. **Start Command:**
   ```
   npm start
   ```

4. **Environment Variables** (make sure these are set):
   ```
   NODE_ENV=production
   PORT=5000
   DB_HOST=167.99.181.177
   DB_NAME=bmislandhost_plan_swgoh
   DB_USER=bmislandhost_bradley
   DB_PASSWORD=DragonFly$2025
   JWT_SECRET=b7f2c4e9a1d84f3c92e0f6b1a7c3d9e4f2a8b6c1e7d4f9a3c5b2e0d7f4a9c1e3
   SWGOH_API_KEY=3a8ac
   ```

## What the Build Command Does

1. `npm install` - Installs backend dependencies
2. `cd client && npm install` - Installs frontend dependencies
3. `npm run build` - Builds the React app (creates `client/build/` folder)

## After Fixing

1. **Push the `render.yaml` file to GitHub:**
   ```bash
   git add render.yaml
   git commit -m "Add Render.com configuration"
   git push
   ```

2. **In Render.com:**
   - If using render.yaml: Click "Manual Deploy" → "Clear build cache & deploy"
   - If manual: Update the build command and redeploy

3. **Wait for deployment** (5-10 minutes)

4. **Test:** Visit your Render.com URL - it should work!

## Troubleshooting

**Still getting the error?**
- Check the build logs in Render.com - make sure the build completed successfully
- Verify `client/build/index.html` exists in the build logs
- Make sure `NODE_ENV=production` is set

**Build fails?**
- Check that all dependencies are in `package.json`
- Verify Node.js version (should be 16+)
- Check build logs for specific errors

