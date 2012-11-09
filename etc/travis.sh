#!/bin/bash

php app/console --env=install doctrine:database:drop  --force
php app/console --env=install doctrine:database:create
php app/console --env=install doctrine:schema:create
php app/console --env=install doctrine:fixtures:load --no-interaction
php app/console --env=install dbal:import etc/sql/demo_tax.sql
php app/console --env=install dbal:import etc/sql/mysql_demo.sql
