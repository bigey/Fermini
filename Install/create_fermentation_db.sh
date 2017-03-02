#!/bin/bash

## MySQL connection credentials
DB_ROOT_USER='root'
DB_ROOT_PASS=<root-mysql-user-password>

## User and database creation script
CREATE_FERMENTATION_DB='fermini_db_creation.sql'

rm -f mysql.log

## Running the script under 'root' privileges
mysql -h localhost \
 --user=$DB_ROOT_USER \
 --password=$DB_ROOT_PASS \
 < $CREATE_FERMENTATION_DB \
 >> mysql.log 2>&1

exit
