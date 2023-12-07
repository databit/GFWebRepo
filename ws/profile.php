<?php
//If use AngularJS Ajax
if(empty($_POST))
    $_POST = json_decode(file_get_contents('php://input'), true);

//Starting PHP session
session_start();

require_once '../config.php';
include_once '../libs/database.php';

header('Content-Type: application/json; charset=utf-8');

if(isset($_POST['cmd'])){
    switch($_POST['cmd']){
        case 'is-logged':
            if(LOGIN_PUREFTP_SQL){
                echo json_encode(array('success' => !empty($_SESSION['webRepo']['user']), 'username' => $_SESSION['webRepo']['user']['username']));

            } else {
                include_once '../libs/ftp.php';
                if(!empty($_SESSION['webRepo']['user'])){
                    $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);

                    echo json_encode(array('success' => $_mHandler->isConnected() && $_mHandler->isLogged(), 'username' => $_SESSION['webRepo']['user']['username']));

                } else 
                    echo json_encode(array('success' => false));
            }
            break;

        case 'login':
            if(LOGIN_PUREFTP_SQL){
                $sQuery ="  SELECT *
                            FROM `". PURE_FTP_TABLE ."` 
                            WHERE `User` = '{$_POST['username']}' 
                            AND `Password`='". crypt($_POST['password'], '$1$OG4RNwvn$') ."'
                            LIMIT 1;";

                $aValues = Database::instance()->fetchOneRow($sQuery, DB_DATABASE);
                if (!empty($_POST['username']) && $aValues != null) {
                    $_SESSION['webRepo']['user'] = array('username' => $_POST['username'], 'password' => $_POST['password']);
                    echo json_encode(array('success' => true));

                } else 
                    echo json_encode(array('success' => false, 'query' => $sQuery));

            } else {
                include_once '../libs/ftp.php';
                $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_POST['username'], $_POST['password']);        

                if($_mHandler->isConnected() && $_mHandler->isLogged()){
                    $_SESSION['webRepo']['user'] = array('username' => $_POST['username'], 'password' => $_POST['password']);

                    echo json_encode(array('success' => true));

                } else 
                    echo json_encode(array('success' => false , 'is_logged' => $_mHandler->isLogged(), 'error' => $_mHandler->getLastError()));
            }

            break;

        case 'logout':
            unset($_SESSION['webRepo']);
            die(json_encode(array('success' => true)));
            break;

        case 'get-profile':
            $sQuery="   SELECT `User`
                        FROM `". PURE_FTP_TABLE ."`
                        WHERE `User` = '{$_SESSION['webRepo']['user']['username']}'
                        LIMIT 1;";

            echo json_encode(Database::instance()->fetchOneRow($sQuery));
            break;

        case 'edit-profile':
            if(!empty($_POST['passwordNew'])){
                if($_POST['passwordNew'] != $_POST['passwordConfirm'])
                    die(json_encode(array('success' => false, 'error' => 'confirm-not-match')));

                else if(strlen($_POST['passwordNew']) < 8)
                    die(json_encode(array('success' => false, 'error' => 'password-too-short')));

                else {
                    $sQuery = sprintf(" UPDATE `". PURE_FTP_TABLE ."` 
                                        SET `Password` = '%s'
                                         WHERE `User` = '%s';",
                                        crypt($_POST['passwordNew'], '$1$OG4RNwvn$'), $_SESSION['webRepo']['user']['username'], '');

                    $_SESSION['webRepo']['user']['password'] = $_POST['passwordNew'];
                }
            } else {
                /*$sQuery = sprintf("	UPDATE `". PURE_FTP_TABLE ."` 
                                    SET `realname` = '%s'
                                     WHERE `id` = '%d';",
                                   Text::toUtf8($_POST['realname']), $_SESSION['icecode']['user']['id'] );
            
                $_SESSION['icecode']['user']['realname'] = $_POST['realname']; */
            }

            echo json_encode(array('success' => Database::instance()->doQuery($sQuery)));
 
            break;
    }

    return;
}

?>
