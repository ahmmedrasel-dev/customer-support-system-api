# 🚀 Customer Support API - Render Deployment Guide

## Files Created for Deployment

### 1. **Dockerfile**

-   PHP 8.2 with Apache setup
-   All required PHP extensions (SQLite, GD, etc.)
-   Composer dependency installation
-   Proper file permissions

### 2. **docker/apache.conf**

-   Apache virtual host configuration
-   Document root set to Laravel's public folder
-   Enables .htaccess rewrite rules

### 3. **docker/start.sh**

-   Deployment startup script
-   Handles database migrations
-   Sets up environment configuration
-   Optimizes Laravel for production

### 4. **.env.production**

-   Production environment variables
-   SQLite database configuration
-   Pusher broadcasting settings
-   Security optimizations

### 5. **render.yaml**

-   Render deployment configuration
-   Environment variables setup
-   Health check configuration
-   Auto-scaling settings

## 🌐 Deploy to Render

### Step 1: Prepare Your Repository

```bash
# Add all Docker files to git
git add .
git commit -m "Add Docker configuration for Render deployment"
git push origin main
```

### Step 2: Create Render Service

1. **Go to [Render Dashboard](https://render.com/)**
2. **Click "New +" → "Web Service"**
3. **Connect Your GitHub Repository**

    - Select your `customer-support-system-api` repository
    - Branch: `main`

4. **Configure Service Settings:**
    - **Name**: `customer-support-api`
    - **Environment**: `Docker`
    - **Region**: Choose closest to your users
    - **Branch**: `main`
    - **Dockerfile Path**: `./Dockerfile`

### Step 3: Set Environment Variables

In Render dashboard, add these environment variables:

```bash
# App Configuration
APP_NAME=CustomerSupportAPI
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:WrmSyXN8uQSWQ9zkjz0ZlIWNHkuHBLQ70qmkh3cSeO8=

# Database (SQLite for simplicity)
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

# Pusher (Real-time Chat)
PUSHER_APP_ID=2058469
PUSHER_APP_KEY=6a68baf450e0cf971739
PUSHER_APP_SECRET=3ec69ba291c42835445d
PUSHER_APP_CLUSTER=ap2
PUSHER_APP_USE_TLS=true

# Frontend URL (set this after frontend deployment)
FRONTEND_URL=https://your-frontend-url.onrender.com
```

### Step 4: Deploy

1. **Click "Create Web Service"**
2. **Wait for deployment** (takes 5-10 minutes)
3. **Check deployment logs** for any errors

### Step 5: Test Deployment

Once deployed, test these endpoints:

-   **Health Check**: `https://your-api-url.onrender.com/api/health`
-   **Registration**: `POST https://your-api-url.onrender.com/api/register`
-   **Login**: `POST https://your-api-url.onrender.com/api/login`

## 🔧 Database Setup

The deployment automatically:

-   ✅ Creates SQLite database file
-   ✅ Runs all migrations
-   ✅ Seeds initial data (if available)

## 📊 Monitoring

-   **Logs**: Available in Render dashboard
-   **Health Check**: `/api/health` endpoint
-   **Metrics**: CPU, Memory usage in dashboard

## 🔗 Next Steps

1. **Deploy Frontend**: Update frontend with your API URL
2. **Custom Domain**: Add custom domain in Render settings
3. **SSL**: Automatic HTTPS provided by Render
4. **Environment Variables**: Update FRONTEND_URL after frontend deployment

## 🚨 Troubleshooting

### Common Issues:

1. **Build Fails**

    - Check Dockerfile syntax
    - Verify all files exist

2. **Database Errors**

    - SQLite file permissions
    - Migration issues

3. **CORS Errors**
    - Update FRONTEND_URL environment variable
    - Check cors.php configuration

### Logs Access:

```bash
# View deployment logs in Render dashboard
# Or use Render CLI
render logs --service customer-support-api
```

## 📝 Important Notes

-   **Database**: Using SQLite for simplicity (suitable for demos)
-   **File Storage**: Local storage (files won't persist across deployments)
-   **Real-time**: Pusher configured for WebSocket support
-   **Security**: Production environment with disabled debug mode

Your API will be available at: `https://your-service-name.onrender.com`
