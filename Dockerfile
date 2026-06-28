# syntax=docker/dockerfile:1

# ── Stage 1: Frontend asset build ───────────────────────────────────────────
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources/ resources/
COPY app/ app/

RUN npm run build

# ── Stage 2: Production PHP application ─────────────────────────────────────
FROM php:8.4-fpm-alpine

LABEL org.opencontainers.image.source="https://github.com/kwasii1/face-pipeline-ui"

# Install system packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*

# Install PHP extensions bundled with PHP
RUN docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        opcache \
        zip \
        intl \
        bcmath \
        pcntl

# Install Redis via PECL (requires build toolchain)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create application directory
WORKDIR /var/www/html

# Copy application code (excluding items in .dockerignore)
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build/ public/build/

# Install production PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts \
    && composer clear-cache

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Set filesystem permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── PHP configuration ───────────────────────────────────────────────────────
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY <<-'PHPINI' /usr/local/etc/php/conf.d/zz-opcache.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.enable_cli=1
PHPINI

COPY <<-'PHPINI' /usr/local/etc/php/conf.d/zz-uploads.ini
post_max_size = 300M
upload_max_filesize = 300M
max_file_uploads = 100
memory_limit = 512M
max_execution_time = 120
max_input_time = 120
PHPINI

# ── PHP-FPM pool configuration ──────────────────────────────────────────────
COPY <<-'FPMCONF' /usr/local/etc/php-fpm.d/zz-docker.conf
[www]
listen = /var/run/php-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
user = www-data
group = www-data
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500
clear_env = no
catch_workers_output = yes
FPMCONF

# ── Nginx configuration ─────────────────────────────────────────────────────
RUN mkdir -p /run/nginx

COPY <<-'NGINX' /etc/nginx/nginx.conf
worker_processes auto;
error_log /dev/stderr warn;
pid /run/nginx/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /dev/stdout main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 64m;
    server_tokens off;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml+rss
               application/rss+xml font/truetype font/opentype
               application/vnd.ms-fontobject image/svg+xml;

    server {
        listen 80 default_server;
        listen [::]:80 default_server;
        server_name _;
        root /var/www/html/public;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
        add_header X-XSS-Protection "1; mode=block";

        index index.php;

        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_buffer_size 128k;
            fastcgi_buffers 256 16k;
            fastcgi_busy_buffers_size 256k;
            fastcgi_read_timeout 300;
            fastcgi_hide_header X-Powered-By;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }

        location ~ ^/livewire-[a-f0-9]+/ {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~* \.(?:css|js|map|jpg|jpeg|gif|png|ico|svg|woff2?|ttf|eot)$ {
            expires 1y;
            access_log off;
            add_header Cache-Control "public, immutable";
        }
    }
}
NGINX

# ── Supervisor configuration (all services) ───────────────────────────────────
COPY <<-'SUPERVISOR' /etc/supervisor/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=10

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=20

[program:horizon]
command=php /var/www/html/artisan horizon
user=www-data
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=30

[program:reverb]
command=php /var/www/html/artisan reverb:start --no-interaction
user=www-data
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=40
SUPERVISOR

# ── Supervisor configuration (web only) ──────────────────────────────────────
COPY <<-'SUPERVISORWEB' /etc/supervisor/supervisord-web.conf
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=10

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
priority=20
SUPERVISORWEB

# ── Entrypoint ──────────────────────────────────────────────────────────────
COPY <<-'ENTRYPOINT' /usr/local/bin/docker-entrypoint.sh
#!/bin/sh
set -e

# Cache Laravel bootstrap files (run once, regardless of role)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create public storage symlink
php artisan storage:link --force 2>/dev/null || true

# Optionally run migrations (set RUN_MIGRATIONS=true at runtime)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

# Dispatch based on CONTAINER_ROLE
case "${CONTAINER_ROLE:-all}" in
    web)
        exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord-web.conf
        ;;
    horizon)
        exec php /var/www/html/artisan horizon
        ;;
    reverb)
        exec php /var/www/html/artisan reverb:start --no-interaction
        ;;
    scheduler)
        exec php /var/www/html/artisan schedule:work
        ;;
    *)
        exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
        ;;
esac
ENTRYPOINT

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
