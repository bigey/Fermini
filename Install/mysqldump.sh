#!/bin/bash

DRIVE=<backup-drive>
USER=<database-user>
PASSWORD=<database-user-password>
DATABASE=Fermini

DATE=$(date +%d-%m-%Y-%H-%M)
FILE=$DRIVE/sauvegarde_$DATE
LOG=$HOME/mysqldump.log

cat > $LOG <<EOF
/** Debut de la sauvegarde du $DATE **/
EOF

mysqldump --quick --verbose \
	--databases $DATABASE  \
	--user=$USER \
	--password=$PASSWORD \
	--result-file=$FILE 2>> $LOG

cd $DRIVE
rm -f *.sql
mv $FILE $FILE.sql

cat >> $LOG <<EOF
/** Fin de la sauvegarde du $DATE **/
EOF

exit;