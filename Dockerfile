# PHP 8.2 ve Apache kullanan resmi imajı al
FROM php:8.2-apache

# PostgreSQL sürücülerini yükle ve aktif et
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Apache'nin mod_rewrite modülünü aç (URL yönlendirmeleri için şart)
RUN a2enmod rewrite

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Tüm proje dosyalarını konteyner içine kopyala
COPY . /var/www/html/

# Apache'ye dışarıdan erişim izni (Port 80)
EXPOSE 80