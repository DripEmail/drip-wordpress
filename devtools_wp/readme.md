# Instructions for developing locally

## Setting up your environment

You need Docker (with `docker-compose`) installed.

Spin up Docker and WordPress by running `setup.sh` in the `devtools_wp` directory:

```bash
cd devtools_wp/
./setup.sh
```

This builds a WordPress 7.1 / PHP 8.5 container, installs WordPress, activates the
plugin (the repo root is bind-mounted to `wp-content/plugins/drip-wordpress`), installs
composer + PHP_CodeSniffer/PHPCompatibilityWP, and dumps the fresh database to
`db_data/dump.sql`.

You can access the test site at http://localhost:3008/

- Admin: http://localhost:3008/wp-admin
- Username: `drip`
- Password: `abc1234567890`

MySQL is exposed on `localhost:3308` (user/password/database: `wordpress`).

## Shell access / WP-CLI

```bash
cd devtools_wp/
docker-compose exec web bash
wp --allow-root plugin list
```

## Checking PHP compatibility

```bash
cd devtools_wp/
docker-compose exec web /var/www/html/vendor/bin/phpcs -p --extensions=php \
  --standard=PHPCompatibilityWP --runtime-set testVersion 7.4- \
  --ignore=devtools_wp wp-content/plugins/drip-wordpress
```

## Resetting the database

Restores the database to the snapshot taken at the end of `setup.sh`:

```bash
cd devtools_wp/
./reset.sh
```

## Full reset

```bash
cd devtools_wp/
docker-compose down
rm -rf db_data/
```

Then run `setup.sh` to bring a clean instance back up.
