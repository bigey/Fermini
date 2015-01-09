#!/bin/bash

## Parametres de connection mysql
DB_ROOT_USER='root'
DB_ROOT_PASS=<root-mysql-user-password>

## Script SQL de creation de la DB
CREATE_FERMENTATION_DB='fermini_db_creation.sql'

rm -f mysql.log

## Creation de la DB Fermentation
mysql -h localhost \
 --user=$DB_ROOT_USER \
 --password=$DB_ROOT_PASS \
 < $CREATE_FERMENTATION_DB \
 >> mysql.log 2>&1

exit
