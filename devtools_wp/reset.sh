#!/bin/bash
set -e

cd "$(dirname "$0")"

docker-compose exec -T -e MYSQL_PWD=wordpress db mysql -u wordpress wordpress -e "DROP DATABASE wordpress; CREATE DATABASE wordpress"
docker-compose exec -T -e MYSQL_PWD=wordpress db mysql -u wordpress wordpress < db_data/dump.sql
