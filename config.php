<?php
// Dati Accesso al Database MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_DATABASE', 'pureftpd');

define('FTP_HOST', '127.0.0.1');
define('FTP_USER_ID', '2001');
define('FTP_GROUP_ID', '2001');
define('FTP_ROOT_PATH', '/var/www/users/');
define('FTP_CRYPT_SALT', '$1$OG4RNwvn$');

define('LOGIN_PUREFTP_SQL', true);
define('PURE_FTP_TABLE', 'users');

//define('SERVER_NAME', 'I.T.S. "Pietro Branchina"');
define('SERVER_NAME', 'My Hosting');
?>