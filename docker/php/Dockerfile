FROM php:8.2-apache

# 한국어 로케일 및 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    locales \
    vim \
    curl \
    wget \
    git \
    unzip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# 한국어 로케일 설정
RUN sed -i '/ko_KR.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG ko_KR.UTF-8
ENV LANGUAGE ko_KR:ko
ENV LC_ALL ko_KR.UTF-8

# PHP 확장 모듈 설치 (Laravel에 필요한 것들)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml

# Composer 설치 (PHP 패키지 매니저)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# Apache 설정 (DocumentRoot 변경)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 권한 설정
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# 포트 노출
EXPOSE 80

# Apache 시작
CMD ["apache2-foreground"]