<?php
//If use AngularJS Ajax
if(empty($_POST))
    $_POST = json_decode(file_get_contents('php://input'), true);

//Starting PHP session
session_start();

require_once '../config.php';
include_once '../libs/database.php';

if(isset($_POST['cmd'])){
    switch($_POST['cmd']){
        case 'is-logged':
            if(LOGIN_PUREFTP_SQL){
                echo json_encode(array('success' => !empty($_SESSION['gfWebRepo']['user'])));

            } else {
                include_once '../libs/ftp.php';
                if(!empty($_SESSION['gfWebRepo']['user'])){
                    $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

                    echo json_encode(array('success' => $_mHandler->isConnected()));//->login($_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password'])));

                } else 
                    echo json_encode(array('success' => false));
            }
            break;
            
        case 'login':
            if(LOGIN_PUREFTP_SQL){
                $sQuery ="	SELECT *
                            FROM `ftpd` 
                            WHERE `User` = '{$_POST['username']}' 
                            AND `Password`='".md5($_POST['password'])."'
                            LIMIT 1;";

                $aValues = Database::instance()->fetchOneRow($sQuery, DB_DATABASE);
                if (!empty($_POST['username']) && $aValues != null) {
                    $_SESSION['gfWebRepo']['user'] = array(	'username' =>	$aValues['User'],
                                                            'password' =>	$aValues['Password']);

                    echo json_encode(array('success' => true));

                } else 
                    echo json_encode(array('success' => false));

            } else {
                include_once '../libs/ftp.php';
                $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_POST['username'], $_POST['password']);        

                if($_mHandler->isConnected()){
                    $_SESSION['gfWebRepo']['user'] = array(	'username' =>	$_POST['username'],
                                                            'password' =>	$_POST['password']);

                    echo json_encode(array('success' => true));

                } else 
                    echo json_encode(array('success' => false));
            }

            break;

        case 'logout':
            unset($_SESSION['gfWebRepo']);
            die(json_encode(array('success' => true)));
            break;

        case 'get-profile':
            $sQuery="   SELECT `User`
                        FROM `ftpd`
                        WHERE `User` = '{$_SESSION['gfWebRepo']['user']['username']}'
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
                    $sQuery = sprintf("	UPDATE `ftpd` 
                                        SET `Password` = '%s'
                                         WHERE `User` = '%s';",
                                        md5($_POST['passwordNew']), $_SESSION['gfWebRepo']['user']['username']);

                    $_SESSION['gfWebRepo']['user']['password'] = $_POST['passwordNew'];
                }
            } else {
                /*$sQuery = sprintf("	UPDATE `users` 
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