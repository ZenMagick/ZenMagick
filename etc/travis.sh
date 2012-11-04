#!/bin/bash

php app/console doctrine:database:drop --env=install --force
php app/console doctrine:database:create --env=install
# the doctrine schema breaks the original SQL and also means we are missing the
# default configuration settings that are loaded from the zc_install file
#- php app/console doctrine:schema:create
php app/console --env=install dbal:import vendor/zenmagick/zencart/zc_install/sql/mysql_zencart.sql
php app/console --env=install dbal:import etc/sql/mysql_demo.sql
