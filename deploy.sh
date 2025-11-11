#!/bin/bash

# IMS Deployment Script
# This script helps deploy the IMS application to a production server

echo "========================================="
echo "IMS Deployment Script"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    print_warning "This script should be run with sudo privileges for some operations"
fi

# Step 1: Check dependencies
echo "Step 1: Checking dependencies..."
command -v php >/dev/null 2>&1 || { print_error "PHP is not installed. Please install PHP 8.1+"; exit 1; }
command -v composer >/dev/null 2>&1 || { print_error "Composer is not installed. Please install Composer"; exit 1; }
command -v mysql >/dev/null 2>&1 || { print_error "MySQL is not installed. Please install MySQL"; exit 1; }
print_success "All dependencies are installed"

# Step 2: Set deployment directory
read -p "Enter deployment directory (default: /var/www/ims): " DEPLOY_DIR
DEPLOY_DIR=${DEPLOY_DIR:-/var/www/ims}

if [ ! -d "$DEPLOY_DIR" ]; then
    print_error "Directory $DEPLOY_DIR does not exist"
    read -p "Create directory? (y/n): " create_dir
    if [ "$create_dir" = "y" ]; then
        sudo mkdir -p $DEPLOY_DIR
        print_success "Directory created"
    else
        exit 1
    fi
fi

cd $DEPLOY_DIR

# Step 3: Install composer dependencies
echo ""
echo "Step 2: Installing composer dependencies..."
composer install --optimize-autoloader --no-dev
print_success "Dependencies installed"

# Step 4: Setup environment
echo ""
echo "Step 3: Setting up environment..."
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env
        print_success ".env file created from .env.production"
    else
        cp .env.example .env
        print_success ".env file created from .env.example"
    fi
    print_warning "Please edit .env file with your production settings"
    read -p "Press Enter to continue after editing .env..."
else
    print_info ".env file already exists"
fi

# Step 5: Generate app key if not set
echo ""
echo "Step 4: Generating application key..."
php artisan key:generate --force
print_success "Application key generated"

# Step 6: Database setup
echo ""
echo "Step 5: Database setup..."
read -p "Have you created the database and imported the SQL file? (y/n): " db_ready
if [ "$db_ready" != "y" ]; then
    print_warning "Please create database and import SQL file before continuing"
    read -p "Database name: " db_name
    read -p "Database user: " db_user
    read -sp "Database password: " db_pass
    echo ""
    
    print_info "Creating database..."
    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -p -e "CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_pass';"
    mysql -u root -p -e "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'localhost';"
    mysql -u root -p -e "FLUSH PRIVILEGES;"
    
    read -p "Path to SQL backup file: " sql_file
    if [ -f "$sql_file" ]; then
        mysql -u $db_user -p$db_pass $db_name < $sql_file
        print_success "Database imported"
    else
        print_error "SQL file not found"
    fi
fi

# Step 7: Run migrations
echo ""
echo "Step 6: Running migrations..."
php artisan migrate --force
print_success "Migrations completed"

# Step 8: Set permissions
echo ""
echo "Step 7: Setting file permissions..."
sudo chown -R www-data:www-data $DEPLOY_DIR
sudo chmod -R 755 $DEPLOY_DIR
sudo chmod -R 775 $DEPLOY_DIR/storage
sudo chmod -R 775 $DEPLOY_DIR/bootstrap/cache
print_success "Permissions set"

# Step 9: Cache optimization
echo ""
echo "Step 8: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Caches created"

# Step 10: Web server configuration
echo ""
echo "Step 9: Web server configuration..."
read -p "Configure Nginx? (y/n): " config_nginx
if [ "$config_nginx" = "y" ]; then
    read -p "Domain name: " domain_name
    
    cat > /tmp/ims-nginx.conf <<EOF
server {
    listen 80;
    server_name $domain_name www.$domain_name;
    root $DEPLOY_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
}
EOF

    sudo cp /tmp/ims-nginx.conf /etc/nginx/sites-available/ims
    sudo ln -sf /etc/nginx/sites-available/ims /etc/nginx/sites-enabled/ims
    sudo nginx -t && sudo systemctl restart nginx
    print_success "Nginx configured"
    
    # SSL setup
    read -p "Setup SSL with Let's Encrypt? (y/n): " setup_ssl
    if [ "$setup_ssl" = "y" ]; then
        sudo certbot --nginx -d $domain_name -d www.$domain_name
        print_success "SSL certificate installed"
    fi
fi

# Final message
echo ""
echo "========================================="
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo "========================================="
echo ""
echo "Access your application at:"
echo "- Dashboard: https://$domain_name/ims-dashboard/login.php"
echo "- API: https://$domain_name/api"
echo ""
echo "Next steps:"
echo "1. Verify .env configuration"
echo "2. Test the application"
echo "3. Setup automated backups"
echo "4. Configure monitoring"
echo ""
print_warning "Don't forget to:"
echo "- Set APP_DEBUG=false in .env"
echo "- Set APP_ENV=production in .env"
echo "- Use strong database passwords"
echo "- Keep your system updated"
