#!/bin/bash
clear
# Regular Colors
Color_Off='\033[0m'       # Text Reset
Green='\033[0;32m'        # Green
echo -n "* Installazione del server FTP, Apache2, PHP, MySQL e di PHP, PHPMyAdmin e GIT in corso ..." 
sudo apt-get -qq install -y pure-ftpd-mysql mysql-server mysql-client phpmyadmin apache2 php git openssl
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Popolazione del database MySQL con le tabelle utenti FTP in corso ..." 
sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS pureftpd DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;USE pureftpd;DROP TABLE IF EXISTS users;CREATE TABLE users (user varchar(32) NOT NULL DEFAULT '', name varchar(100) DEFAULT NULL, email varchar(150) DEFAULT NULL, status tinyint(1) NOT NULL DEFAULT '1', password varchar(106) NOT NULL DEFAULT '', uid varchar(11) NOT NULL DEFAULT '-1', gid varchar(11) NOT NULL DEFAULT '-1', dir varchar(128) NOT NULL DEFAULT '', is_admin tinyint(1) NOT NULL DEFAULT '0', ul_bandwidth smallint(6) NOT NULL DEFAULT '0', dl_bandwidth smallint(6) NOT NULL DEFAULT '0', comment tinytext NOT NULL, ipaccess varchar(15) NOT NULL DEFAULT '*', quota_size smallint(6) NOT NULL DEFAULT '0', quota_files int(11) NOT NULL DEFAULT '0', deleted tinyint(1) NOT NULL DEFAULT '0') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;ALTER TABLE users  ADD PRIMARY KEY (user), ADD UNIQUE KEY User (user);INSERT INTO users (user, name, email, status, is_admin, password, uid, gid, dir, ul_bandwidth, dl_bandwidth, comment, ipaccess, quota_size, quota_files) VALUES ('admin', 'Amministratore', 'admin@loacalhost.org', '1', '1', '\$1\$OG4RNwvn\$HKMVueFdNd9g2aarmiGK51','2001','2001','/var/www/html/users/admin/','0','0','','*','0','0'); CREATE USER 'pureftpd'@'localhost' IDENTIFIED WITH mysql_native_password BY 'O2t11[A8yZj#';GRANT ALL PRIVILEGES ON *.* TO 'pureftpd'@'localhost'; USE mysql; UPDATE user SET Grant_priv = 'Y' WHERE user.Host = 'localhost' AND user.User = 'pureftpd'; FLUSH PRIVILEGES;"
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Download dell'ultima versione di WebRepo in corso ..." 
sudo git clone https://github.com/databit/WebRepo.git /var/www/html/WebRepo 
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Configurazione del server FTP in corso ..." 
sudo echo "yes" > /etc/pure-ftpd/conf/ChrootEveryone
sudo echo "yes" > /etc/pure-ftpd/conf/CreateHomeDir
sudo echo "yes" > /etc/pure-ftpd/conf/DontResolve
sudo echo "1" > /etc/pure-ftpd/conf/TLS
sudo openssl req -x509 -nodes -days 7300 -newkey rsa:2048 -keyout /etc/ssl/private/pure-ftpd.pem -out /etc/ssl/private/pure-ftpd.pem -subj "/C=IT/ST=Rome/L=Rome/O=MIM/OU=IT Computer Science Labs/CN=localhost.org/emailAddress=admin@localhost.org" 2>/dev/null
sudo chmod 600 /etc/ssl/private/pure-ftpd.pem
sudo useradd -u 2001 -s /bin/false -d /bin/null -c "pureftpd user" -g www-data ftpuser 
sudo mv /var/www/html/WebRepo/setup/pureftpd-mysql.conf /etc/pure-ftpd/db/mysql.conf 
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Configurazione del webserver Apache in corso ..." 
sudo mv /var/www/html/WebRepo/setup/apache.conf /etc/apache2/sites-enabled/000-default.conf
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Creazione delle cartelle utenti in corso ..." 
sudo mkdir /var/www/html/users/
sudo chmod -R 775 /var/www/html
sudo chown -R ftpuser:www-data /var/www/html 
sudo mkdir /var/www/html/users/admin
sudo chmod -R 775 /var/www/html/users/admin/
sudo chown -R ftpuser:www-data /var/www/html/users/admin/
echo "[${Green}OK${Color_Off}]\n"
echo -n "* Riavvio dei servizi in corso ..." 
sudo service pure-ftpd-mysql restart 
sudo service apache2 restart 
echo "[${Green}OK${Color_Off}]\n"