#!/bin/bash
# setup.sh - Automated setup script

set -e

echo "🚀 Setting up Yet Another Forex Application..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create project structure
print_status "Creating project structure..."
mkdir -p forex-app/{backend,frontend}
cd forex-app

# Create Laravel backend structure
print_status "Setting up Laravel backend..."
mkdir -p backend/{app/{Http/{Controllers,Requests},Services},config,routes}

# Create backend files with content
cat > backend/Dockerfile.backend << 'EOF'
# Content from Dockerfile.backend artifact
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apk del --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Copy Nginx configuration
RUN echo 'server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
    
    location ~ /\.ht {
        deny all;
    }
}' > /etc/nginx/http.d/default.conf

# Copy supervisor configuration
RUN echo '[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php]
command=php-fpm -F
user=www
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/php.log

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/nginx.log
' > /etc/supervisor/conf.d/supervisord.conf

# Create required directories
RUN mkdir -p /var/log/supervisor \
    /var/log/nginx \
    /var/www/storage/logs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache

# Set permissions
RUN chown -R www:www /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy environment file
COPY .env.docker /var/www/.env

# Generate application key
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache

# Change current user to www
USER www

# Expose port 80
EXPOSE 80

# Switch back to root for supervisor
USER root

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

# Create React frontend structure
print_status "Setting up React frontend..."
mkdir -p frontend/src/{components,services}

# Create frontend Dockerfile
cat > frontend/Dockerfile.frontend << 'EOF'
FROM node:18-alpine as builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY . .

# Build the app
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy built app from builder stage
COPY --from=builder /app/build /usr/share/nginx/html

# Copy nginx configuration
RUN echo 'server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;
    
    # Handle client-side routing
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
}' > /etc/nginx/conf.d/default.conf

# Remove default nginx website
RUN rm -rf /usr/share/nginx/html/index.html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
EOF

# Create Docker Compose file
print_status "Creating Docker Compose configuration..."
cat > docker-compose.yml << 'EOF'
version: '3.8'

services:
  # MySQL Database
  mysql:
    image: mysql:8.0
    container_name: forex_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: forex_db
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_USER: forex_user
      MYSQL_PASSWORD: forex_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - forex_network

  # Laravel Backend
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile.backend
    container_name: forex_backend
    restart: unless-stopped
    ports:
      - "8000:80"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=forex_db
      - DB_USERNAME=forex_user
      - DB_PASSWORD=forex_password
    depends_on:
      - mysql
    volumes:
      - ./backend:/var/www
      - /var/www/vendor
      - /var/www/node_modules
    networks:
      - forex_network

  # React Frontend
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile.frontend
    container_name: forex_frontend
    restart: unless-stopped
    ports:
      - "3000:80"
    environment:
      - REACT_APP_API_URL=http://localhost:8000/api
    depends_on:
      - backend
    networks:
      - forex_network

  # Redis for caching (optional)
  redis:
    image: redis:7-alpine
    container_name: forex_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - forex_network

volumes:
  mysql_data:

networks:
  forex_network:
    driver: bridge
EOF

# Create environment files
print_status "Creating environment files..."
cat > backend/.env.docker << 'EOF'
APP_NAME="Yet Another Forex API"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=forex_db
DB_USERNAME=forex_user
DB_PASSWORD=forex_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Forex API Configuration
FOREX_API_KEY=your_api_key_here
FOREX_API_URL=https://api.exchangerate-api.com/v4

# CORS Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
EOF

cat > frontend/.env.production << 'EOF'
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_APP_NAME="Yet Another Forex"
GENERATE_SOURCEMAP=false
EOF

# Create quick start script
cat > start.sh << 'EOF'
#!/bin/bash
echo "🚀 Starting Yet Another Forex Application..."

# Build and start all services
docker-compose up --build -d

echo "✅ Application is starting..."
echo "📱 Frontend: http://localhost:3000"
echo "🔧 Backend API: http://localhost:8000"
echo "🗄️ MySQL: localhost:3306"

# Show logs
docker-compose logs -f
EOF

chmod +x start.sh

# Create stop script
cat > stop.sh << 'EOF'
#!/bin/bash
echo "🛑 Stopping Yet Another Forex Application..."
docker-compose down
echo "✅ Application stopped."
EOF

chmod +x stop.sh

# Create reset script
cat > reset.sh << 'EOF'
#!/bin/bash
echo "🔄 Resetting Yet Another Forex Application..."
docker-compose down -v
docker system prune -f
echo "✅ Application reset completed."
EOF

chmod +x reset.sh

print_status "Setup completed! 🎉"
print_status ""
print_status "Next steps:"
print_status "1. Copy your Laravel and React code into backend/ and frontend/ folders"
print_status "2. Run: ./start.sh"
print_status ""
print_status "Available commands:"
print_status "  ./start.sh  - Start the application"
print_status "  ./stop.sh   - Stop the application"  
print_status "  ./reset.sh  - Reset everything (removes data)"
print_status ""
print_status "URLs:"
print_status "  Frontend: http://localhost:3000"
print_status "  Backend:  http://localhost:8000"