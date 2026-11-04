#!/bin/bash
set -e

cd "$(dirname "$0")"

# Spin up a new instance of WordPress
# Add --build when you need to rebuild the Dockerfile.
docker-compose up -d --build

port=$(docker-compose port web 80 | cut -d':' -f2)

# Wait for the DB to be up.
docker-compose exec -T db /bin/bash -c 'while ! mysql --protocol TCP -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "show databases;" > /dev/null 2>&1; do sleep 1; done'

# Wait for the WordPress image entrypoint to copy core files into place.
docker-compose exec -T web /bin/bash -c 'while [ ! -f /var/www/html/wp-config.php ]; do sleep 1; done'

wordpress_setup_script=$(cat <<SCRIPT
cd /var/www/html/ && \
/usr/local/bin/wp core install --url="http://localhost:$port" --title="drip_wordpress_test" --admin_user="drip" --admin_email="drip@example.com" --admin_password="abc1234567890" --skip-email && \
/usr/local/bin/wp plugin activate drip-wordpress
SCRIPT
)

composer="php /var/www/html/composer.phar"
update_bashrc_script=$(cat <<SCRIPT
if ! grep -q 'composer.phar' /root/.bashrc; then
  echo 'alias composer="$composer"' >> /root/.bashrc
fi
if ! grep -q 'composer/vendor' /root/.bashrc; then
  echo 'PATH=/root/.composer/vendor/bin:\${PATH}' >> /root/.bashrc
fi
if ! grep -q PHP_MEMORY_LIMIT /root/.bashrc; then
  echo 'export PHP_MEMORY_LIMIT=1G' >> /root/.bashrc
fi
SCRIPT
)

docker-compose exec -T -u root web /bin/bash -c "chown www-data wp-content"
docker-compose exec -T -u www-data web /bin/bash -c "$wordpress_setup_script"
docker-compose exec -T -u root web /bin/bash -c "$(cat install-composer.sh)"
docker-compose exec -T -u root web /bin/bash -c "$update_bashrc_script"
docker-compose exec -T -u root web /bin/bash -c "
  [ -f composer.json ] || echo '{}' > composer.json
  $composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
"
docker-compose exec -T -u root web /bin/bash -c "
  $composer global require 'squizlabs/php_codesniffer=*' && \
    $composer require --dev \
      'dealerdirect/phpcodesniffer-composer-installer:^0.7' \
      'phpcompatibility/phpcompatibility-wp:*' && \
    $composer install
"
# PHP compatibility check, e.g. from inside the container (docker-compose exec web bash):
#   /var/www/html/vendor/bin/phpcs -p --extensions=php --standard=PHPCompatibilityWP \
#     --runtime-set testVersion 7.4- --ignore=devtools_wp wp-content/plugins/drip-wordpress

echo "Backing up database for later reset"
mkdir -p db_data
touch db_data/dump.sql
docker-compose exec -T -e MYSQL_PWD=wordpress db mysqldump --no-tablespaces -u wordpress wordpress > db_data/dump.sql

echo "WordPress is up at http://localhost:$port/wp-admin (drip / abc1234567890)"
