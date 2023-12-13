sudo apt-get install -y pure-ftpd-mysql mysql-server mysql-client phpmyadmin apache2 php git
sudo groupadd -g 2001 ftpgroup
sudo useradd -u 2001 -s /bin/false -d /bin/null -c "pureftpd user" -g ftpgroup ftpuser
usermod -a -G www-data ftpuser
sudo mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS `pureftpd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pureftpd`;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(106) NOT NULL DEFAULT '',
  `uid` varchar(11) NOT NULL DEFAULT '-1',
  `gid` varchar(11) NOT NULL DEFAULT '-1',
  `dir` varchar(128) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `ul_bandwidth` smallint(6) NOT NULL DEFAULT '0',
  `dl_bandwidth` smallint(6) NOT NULL DEFAULT '0',
  `comment` tinytext NOT NULL,
  `ipaccess` varchar(15) NOT NULL DEFAULT '*',
  `quota_size` smallint(6) NOT NULL DEFAULT '0',
  `quota_files` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `users`
  ADD PRIMARY KEY (`user`),
  ADD UNIQUE KEY `User` (`user`);
CREATE USER 'pureftpd'@'localhost' IDENTIFIED WITH mysql_native_password BY 'O2t11[A8yZj#';
GRANT ALL PRIVILEGES ON *.* TO 'pureftpd'@'localhost' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0; 
FLUSH PRIVILEGES;
INSERT INTO `users` (`user`, `name`, `email`, `status`, `is_admin`, `password`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files`) VALUES ('admin', 'Amministratore', 'admin@loacalhost.org', '1', '1', '$1$OG4RNwvn$HKMVueFdNd9g2aarmiGK51','2001','2001','/var/www/html/users/','0','0','','*','0','0');"
sudo echo "yes" > /etc/pure-ftpd/conf/ChrootEveryone
sudo echo "yes" > /etc/pure-ftpd/conf/CreateHomeDir
sudo echo "yes" > /etc/pure-ftpd/conf/DontResolve
sudo git clone https://github.com/databit/WebRepo.git /var/www/html
sudo mv /var/www/html/WebRepo/pureftpd-mysql.conf /etc/pure-ftpd/db/mysql.conf
sudo mv /var/www/html/WebRepo/apache.conf /etc/apache2/sites-enabled/000-default.conf
sudo mkdir /var/www/html/users/
sudo chmod -R 775 /var/www/html
sudo chown -R ftpuser:www-data /var/www/html 
sudo mkdir /var/www/html/users/admin
sudo chmod -R 775 /var/www/html/admin/
#sudo chown -R ftpuser:www-data /var/www/html/admin/
sudo service pure-ftpd-mysql restart
sudo service apache2 restart
