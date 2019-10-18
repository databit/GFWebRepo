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
        case 'login':
            if(LOGIN_PUREFTP_SQL){
                $sQuery ="	SELECT *
                            FROM `users` 
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

        case 'get-profile':
            $sQuery="   SELECT `username`, `realname`
                        FROM `users`
                        WHERE `id` = '{$_SESSION['icecode']['user']['id']}'
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
                    $sQuery = sprintf("	UPDATE `users` 
                                        SET `password` = '%s',
                                            `realname` = '%s'
                                         WHERE `id` = '%d';",
                                        md5($_POST['passwordNew']));// ,Text::toUtf8($_POST['realname']), $_SESSION['icecode']['user']['id'] );
                                        
                    $_SESSION['icecode']['user']['realname'] = $_POST['realname'];
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