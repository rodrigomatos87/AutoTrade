#!/bin/bash

cd /var/www/html
mkdir process
mkdir data_realtime
mkdir data_historical

npm install
npm install wc@latest
npm install pm2@latest

chown -R www-data:www-data /var/www/html

pm2 start binance_websocket.js --name "binance_websocket"
pm2 save
pm2 startup

chmod +x config_mariadb.sh
sh config_mariadb.sh

rm -fr config_mariadb.sh
rm -fr install.sh

exit;