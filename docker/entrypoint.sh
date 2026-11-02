#!/bin/sh
set -e
cat > /var/www/nodeman/.env <<EOF
DBPASSWORD=${DB_PASSWORD}
DEBUG=0
NODEBINARYLOCATION=/usr/bin/node
LOGSLOCATION=/var/log/nodeman/
MISCDATADIR=/var/nodeman/data/
DBADDRESS=db
DBUSER=nodeman
NODEMANDBNAME=nodeman
EOF
chown www-data:www-data /var/www/nodeman/.env
exec apache2-foreground
