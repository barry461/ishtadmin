#!/usr/bin/env bash

set -e
# 处理nginx模板
mkdir -p nginx/out
export SLUG_API SLUG_STAFF SLUG_WWW SLUG_WEB SLUG_NAV DEPLOY_PATH NGINX_V4_PORT NGINX_V6_PORT NGINX_DEF SLUG_DOMAIN_REDIRECT SLUG_DOMAIN_REG
envsubst '${SLUG_API} ${DEPLOY_PATH} ${NGINX_V4_PORT} ${NGINX_V6_PORT} ${NGINX_DEF}' < nginx/api.conf  > nginx/out/api.conf
envsubst '${SLUG_WWW} ${DEPLOY_PATH} ${NGINX_V4_PORT} ${NGINX_V6_PORT} ${NGINX_DEF} ${SLUG_DOMAIN_REDIRECT} ${SLUG_DOMAIN_REG}' < nginx/www.conf > nginx/out/www.conf
envsubst '${SLUG_STAFF} ${DEPLOY_PATH} ${NGINX_V4_PORT} ${NGINX_V6_PORT} ${NGINX_DEF}' < nginx/staff.conf > nginx/out/staff.conf
envsubst '${SLUG_WEB} ${DEPLOY_PATH} ${NGINX_V4_PORT} ${NGINX_V6_PORT} ${NGINX_DEF}' < nginx/web.conf > nginx/out/web.conf
envsubst '${SLUG_NAV} ${DEPLOY_PATH} ${NGINX_V4_PORT} ${NGINX_V6_PORT} ${NGINX_DEF}' < nginx/nav.conf > nginx/out/nav.conf

# 上传代码
rsync -az --delete --exclude='conf/' \
  --exclude='vendor/' --exclude='storage/' \
  --exclude='application/config.php' \
  --exclude='application/modules/Admin/cache/' \
  --exclude='public/upload/' \
  --exclude='public/staff/upload/' \
  --exclude='public/staff/static/'  \
  --exclude='public/www/upload/' \
  --exclude='public/www/themes/' \
  --exclude='public/nav/upload/' \
  --exclude='public/nav/themes/' \
  --exclude='.git/' \
  --exclude='.gitignore' \
  --exclude='readme.md' \
  ./ "$TEST_USER@$TEST_HOST:$DEPLOY_PATH"

# 更新composer
ssh -o StrictHostKeyChecking=no\
  "$TEST_USER@$TEST_HOST"\
  "cd $DEPLOY_PATH && COMPOSER_ALLOW_SUPERUSER=1 composer update --no-dev --optimize-autoloader && chown -R www:www $DEPLOY_PATH"

# 3) 处理nginx和fpm
ssh "$TEST_USER@$TEST_HOST" bash <<EOF
set -e
# 启用（覆盖旧软链，避免与其他项目冲突
sudo ln -sf "${DEPLOY_PATH}/nginx/out/api.conf" "/etc/nginx/conf.d/${PROJECT}-api.conf"
sudo ln -sf "${DEPLOY_PATH}/nginx/out/www.conf" "/etc/nginx/conf.d/${PROJECT}-www.conf"
sudo ln -sf "${DEPLOY_PATH}/nginx/out/staff.conf" "/etc/nginx/conf.d/${PROJECT}-staff.conf"
sudo ln -sf "${DEPLOY_PATH}/nginx/out/web.conf" "/etc/nginx/conf.d/${PROJECT}-web.conf"
sudo ln -sf "${DEPLOY_PATH}/nginx/out/nav.conf" "/etc/nginx/conf.d/${PROJECT}-nav.conf"
# 语法检查 + 热加载
sudo nginx -s reload
sudo service php-fpm reload
EOF
