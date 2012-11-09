#!/bin/bash

php app/console --env=install doctrine:database:drop  --force
php app/console --env=install doctrine:database:create
php app/console --env=install doctrine:schema:create
# @todo: purge with truncate won't work in tables with  foreign keys in mysql 5.5
php app/console --env=install doctrine:fixtures:load --no-interaction --purge-with-truncate
php app/console --env=install dbal:import etc/sql/demo_tax.sql
php app/console --env=install dbal:import etc/sql/mysql_demo.sql
