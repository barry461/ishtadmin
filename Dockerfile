FROM php:8.2-fpm

# 安装 Yaf 扩展
RUN pecl install yaf \
    && docker-php-ext-enable yaf

# 安装其他扩展
RUN docker-php-ext-install pdo_mysql

# 设置工作目录
WORKDIR /var/www/html

# 复制代码
COPY . .

# 设置权限
RUN chown -R www-data:www-data /var/www/html

# 暴露端口
EXPOSE 9000

# 启动 PHP-FPM
CMD ["php-fpm"]
