<?php
$cmd1 = "mysqladmin -u root password '#D0EbUaS702_i8Iniu@yUlgD#'";
$cmd2 = "mysql -u root -p#D0EbUaS702_i8Iniu@yUlgD# -e 'CREATE DATABASE AutoTrade'";
$cmd3 = "mysql -h localhost -u root -p#D0EbUaS702_i8Iniu@yUlgD# -e \"DELETE FROM mysql.user WHERE user='';\"";
$cmd4 = "mysql -h localhost -u root -p#D0EbUaS702_i8Iniu@yUlgD# -e \"DELETE FROM mysql.user WHERE host='localhost.localdomain';\"";
$cmd5 = "mysql -h localhost -u root -p#D0EbUaS702_i8Iniu@yUlgD# -e \"GRANT ALL PRIVILEGES ON * . * TO 'root'@'localhost';\"";
$cmd6 = "mysql -h localhost -u root -p#D0EbUaS702_i8Iniu@yUlgD# -e \"FLUSH PRIVILEGES;\"";
$cmd7 = "chown -R www-data:www-data /var/www/html";

exec($cmd1);
exec($cmd2);
exec($cmd3);
exec($cmd4);
exec($cmd5);
exec($cmd6);
exec($cmd7);

mysqli_query($db, "CREATE TABLE `users` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `user` varchar(255) NOT NULL DEFAULT '',
    `senha` varchar(255) NOT NULL DEFAULT '',
    `nome` varchar(200) DEFAULT NULL,
    `telefone` varchar(45) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `apiKey` varchar(255) DEFAULT NULL,
    `secretKey` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

unlink('/var/www/html/mariadb.php');