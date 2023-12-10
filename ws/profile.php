<?php
//If use AngularJS Ajax
if(empty($_POST))
    $_POST = json_decode(file_get_contents('php://input'), true);

//Starting PHP session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
include_once '../libs/database.php';

header('Content-Type: application/json; charset=utf-8');

if(isset($_POST['cmd'])){
    switch($_POST['cmd']){
        case 'is-logged':
            if(LOGIN_PUREFTP_SQL){
                echo json_encode(array('success' => !empty($_SESSION['webRepo']['user']), 'username' => $_SESSION['webRepo']['user']['username'], 'changePassword' => $_SESSION['webRepo']['user']['change-password']));

            } else {
                include_once '../libs/ftp.php';
                if(!empty($_SESSION['webRepo']['user'])){
                    $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);

                    echo json_encode(array('success' => $_mHandler->isConnected() && $_mHandler->isLogged(), 'username' => $_SESSION['webRepo']['user']['username'], 'changePassword' => $_SESSION['webRepo']['user']['change-password']));

                } else 
                    echo json_encode(array('success' => false));
            }
            break;

        case 'login':
            if(LOGIN_PUREFTP_SQL){
                $sQuery ="  SELECT `password`
                            FROM `". PURE_FTP_TABLE ."` 
                            WHERE `user` = '{$_POST['username']}' 
                            AND `password`='". crypt($_POST['password'], FTP_CRYPT_SALT) ."'
                            LIMIT 1;";

                $sHashedPassword = Database::instance()->fetchOneValue($sQuery, DB_DATABASE);
                if (!empty($_POST['username']) && $sHashedPassword != null) {
                    $_SESSION['webRepo']['user'] = array('username' => $_POST['username'], 'password' => $_POST['password'], 'change-password' => crypt($_POST['username'], FTP_CRYPT_SALT) == $sHashedPassword);
                    echo json_encode(array('success' => true, 'changePassword' => $_SESSION['webRepo']['user']['change-password']));

                } else 
                    echo json_encode(array('success' => false));

            } else {
                include_once '../libs/ftp.php';
                $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_POST['username'], $_POST['password']);        

                if($_mHandler->isConnected() && $_mHandler->isLogged()){
                    
                    $_SESSION['webRepo']['user'] = array('username' => $_POST['username'], 'password' => $_POST['password'], 'change-password' => crypt($_POST['username'], FTP_CRYPT_SALT) == crypt($_POST['password'], FTP_CRYPT_SALT));

                    echo json_encode(array('success' => true, 'changePassword' => $_SESSION['webRepo']['user']['change-password']));

                } else 
                    echo json_encode(array('success' => false , 'is_logged' => $_mHandler->isLogged(), 'error' => $_mHandler->getLastError()));
            }
            break;

        case 'logout':
            unset($_SESSION['webRepo']);
            die(json_encode(array('success' => true)));
            break;

        case 'get-profile':
            $sQuery="   SELECT `user`, `name`, `email`, `dir`
                        FROM `". PURE_FTP_TABLE ."`
                        WHERE `user` = '{$_SESSION['webRepo']['user']['username']}'
                        LIMIT 1;";

            echo json_encode(Database::instance()->fetchOneRow($sQuery));
            break;

        case 'edit-profile':
            if(!empty($_POST['password'])) { 
                if(strlen($_POST['password']) < 8)
                    die(json_encode(array('success' => false, 'error' => 'password-too-short')));

                else if($_SESSION['webRepo']['user']['username'] == $_POST['password'])
                        die(json_encode(array('success' => false, 'error' => 'no-default-password')));
            }
            $sQuery = " UPDATE `". PURE_FTP_TABLE ."`
                        SET ". (!empty($_POST['name']) ? "`name` = '". Database::instance()->escapeString($_POST['name']) ."'," : '') ."
                            ". (!empty($_POST['email']) ? "`email` = '". Database::instance()->escapeString($_POST['email']) ."'," : '') ."
                            ". (!empty($_POST['password']) ? "`password` = '". crypt($_POST['password'], FTP_CRYPT_SALT) ."'," : '') ."
                            `user` = '". Database::instance()->escapeString($_SESSION['webRepo']['user']['username']) ."'
                        WHERE `user` = '". Database::instance()->escapeString($_SESSION['webRepo']['user']['username']) ."';";
            die(json_encode(array('success' => Database::instance()->doQuery($sQuery))));
            break;

        case 'change-password':
            if(!empty($_POST['password'])){
                if(strlen($_POST['password']) < 8)
                    die(json_encode(array('success' => false, 'error' => 'password-too-short')));

                else if( $_SESSION['webRepo']['user']['username'] == $_POST['password'])
                    die(json_encode(array('success' => false, 'error' => 'no-default-password')));

                else {
                    $sQuery = " UPDATE `". PURE_FTP_TABLE ."` 
                                SET `password` = '". crypt($_POST['password'], FTP_CRYPT_SALT) ."'
                                WHERE `user` = '". $_SESSION['webRepo']['user']['username'] ."';";

                    $_SESSION['webRepo']['user']['password'] = $_POST['password'];
                    $_SESSION['webRepo']['user']['change-password'] = false;
                }
            }

            echo json_encode(array('success' => Database::instance()->doQuery($sQuery)));
            break;
    }

    return;
}

?>
