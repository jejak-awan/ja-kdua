#!/bin/bash

# JA-CMS Deployment Script
# This script automates the deployment process for production

set -e  # Exit on error

echo "🚀 Starting JA-CMS Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ .env file not found!${NC}"
    exit 1
fi

# Check if APP_ENV is production
if grep -q "APP_ENV=production" .env; then
    echo -e "${GREEN}✓ Production environment detected${NC}"
else
    echo -e "${YELLOW}⚠ Warning: APP_ENV is not set to production${NC}"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 1: Pull latest code
echo -e "\n${GREEN}📥 Pulling latest code...${NC}"
git pull origin main || git pull origin master

# Step 2: Install/Update dependencies
echo -e "\n${GREEN}📦 Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci

# Step 3: Run migrations
echo -e "\n${GREEN}🗄️  Running database migrations...${NC}"
php artisan migrate --force

# Step 4: Clear and cache config
echo -e "\n${GREEN}⚙️  Optimizing configuration...${NC}"
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Step 5: Build assets
echo -e "\n${GREEN}🏗️  Building production assets...${NC}"
npm run build

# Step 6: Run optimization
echo -e "\n${GREEN}⚡ Running optimization...${NC}"
php artisan optimize

# Step 7: Clear application cache
echo -e "\n${GREEN}🧹 Clearing application cache...${NC}"
php artisan cache:clear

# Step 8: Set permissions
echo -e "\n${GREEN}🔐 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Step 9: Restart queue workers (if using supervisor)
if command -v supervisorctl &> /dev/null; then
    echo -e "\n${GREEN}🔄 Restarting queue workers...${NC}"
    supervisorctl restart laravel-worker:*
fi

# Step 10: Run health check
echo -e "\n${GREEN}🏥 Running health check...${NC}"
php artisan route:list > /dev/null 2>&1 && echo -e "${GREEN}✓ Routes are working${NC}" || echo -e "${RED}❌ Route check failed${NC}"

echo -e "\n${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${YELLOW}⚠ Don't forget to:${NC}"
echo "  - Check queue workers are running"
echo "  - Verify Redis is running (if used)"
echo "  - Test the application"
echo "  - Monitor logs for errors"

