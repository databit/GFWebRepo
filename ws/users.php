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
        case 'list-users':
            $sQuery="   SELECT *
                        FROM `users`
                        ORDER BY `User`;";

            echo json_encode(Database::instance()->fetchAllRows($sQuery, true));
            break;

        case 'search':
            $sQuery = " SELECT *
                        FROM users 
                        WHERE `User` LIKE '%". $_POST['keyword'] ."%'
                        ORDER BY `User`
                        LIMIT 12;";

            echo json_encode(Database::instance()->fetchAllRows($sQuery, true));
            break;
            
        case 'add-user':
            if($_POST['password'] != $_POST['passwordConfirm'])
                echo json_encode(array('success' => false, 'error' => 'confirm-not-match'));

            else if(strlen($_POST['password']) < 8)
                echo json_encode(array('success' => false, 'error' => 'password-too-short'));

            else {
                
                $sQuery="   SELECT `id`
                            FROM `users`
                            WHERE `User` = '". Text::toUtf8($_POST['username'])."'
                            LIMIT 1 ;";

                if (Database::instance()->numRows($sQuery) > 0)
                    echo json_encode(array('success' => false, 'error' => 'username-alredy-taken'));
            
                else {
                    // Avvio la transazione
                    Database::instance()->startTransaction();

                    $bSuccess = Database::instance()->doQuery(" INSERT INTO `users` (`User`, `status`, `Password`, `Uid`, `Gid`, `Dir`, `isAdmin`, `ULBandwidth`, `DLBandwidth`, `comment`, `ipaccess`, `QuotaSize`, `QuotaFiles`)
                                                                VALUES (
                                                                '". Text::toUtf8($_POST['User']) ."', 
                                                                '1', 
                                                                '". crypt($_POST['password'], FTP_CRYPT_SALT) ."',
                                                                '{$_POST['Uid']}',
                                                                '{$_POST['Gid']}',
                                                                '". FTP_ROOT_PATH . $_POST['User'] ."',
                                                                '0', 
                                                                '{$_POST['ULBandwidth']}',
                                                                '{$_POST['DLBandwidth']}',
                                                                '". Text::toUtf8($_POST['comment']) ."',
                                                                '". Text::toUtf8($_POST['ipaccess']) ."',
                                                                '{$_POST['QuotaSize']}',
                                                                '{$_POST['QuotaFiles']}');");

                    // Create DB profile e database
                    $bSuccess = $bSuccess && Database::instance()->doQuery("CREATE USER '{$_POST['User']}'@'localhost' IDENTIFIED WITH mysql_native_password AS '{$_POST['password']}';GRANT USAGE ON *.* TO '{$_POST['User']}'@'localhost' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");

                    for($i=1; $i<=5;$i++){
                        $bSuccess = $bSuccess && Database::instance()->doQuery("CREATE DATABASE `{$_POST['User']}_{$i}`;");
                        $bSuccess = $bSuccess && Database::instance()->doQuery("GRANT ALL PRIVILEGES ON `{$_POST['User']}_{$i}`.* TO '{$_POST['User']}'@'localhost';");
                    }

                    // Create personal folder on webserver
                    if($bSuccess){
                        system('mkdir '. FTP_ROOT_PATH . $_POST['User'], $retval);
                        system('chgrp '. FTP_GROUP_ID .' '. FTP_ROOT_PATH . $_POST['User'], $retval);
                        system('chmod 775 '. FTP_ROOT_PATH . $_POST['User'], $retval);

                        // Committo tutta la transazione e verifico la correttezza di tutti i dati
                        $bSuccess = Database::instance()->tryCommit();

                    } else 
                        // Annullo tutte le modifiche
                        $bSuccess = Database::instance()->rollback();

                    echo json_encode(array('success' => $bSuccess));
                }
            }
            break;

        case 'edit-user':
            if(!empty($_POST['password'])){
                if($_POST['password'] != $_POST['passwordConfirm'])
                    die(json_encode(array('success' => false, 'error' => 'confirm-not-match')));

                else if(strlen($_POST['password']) < 8)
                    die(json_encode(array('success' => false, 'error' => 'password-too-short')));

                else 
                    $sQuery = sprintf(" UPDATE `users` 
                                        SET `password` = '%s',
                                            `realname` = '%s', 
                                            `usertype`  = '%s' 
                                         WHERE `id` = '%d';",
                                        md5($_POST['password']), Text::toUtf8($_POST['realname']), $_POST['usertype'], $_POST['id'] );

            } else {
                $sQuery = sprintf(" UPDATE `users` 
                                    SET `realname` = '%s', 
                                        `usertype`  = '%s' 
                                     WHERE `id` = '%d';",
                                   Text::toUtf8($_POST['realname']), $_POST['usertype'], $_POST['id'] );
            
            }

            echo json_encode(array('success' => Database::instance()->doQuery($sQuery)));
 
            break;

        case 'delete-user':
            $sQuery = " UPDATE `users` 
                        SET `deleted` = '1' 
                        WHERE `id` = {$_POST['id']};";

            echo json_encode(array('success' => Database::instance()->doQuery($sQuery)));
            break;

        case 'change-permission':
            Database::instance()->startTransaction();

            Database::instance()->doQuery("DELETE FROM `acl_users_actions` WHERE `id_user` = '{$_POST['id']}';");

            Database::instance()->doQuery("DELETE FROM `acl_users_menu` WHERE `id_user` = '{$_POST['id']}';");

            $sValues = '';

            foreach($_POST['actions'] as $nAction => $bChecked)
                if($bChecked !== null && $bChecked !== 0)
                    $sValues .= "('{$nAction}', '{$_POST['id']}'),";
                
            if(!empty($sValues)) {
                $sValues[strlen($sValues)-1] = ';';

                Database::instance()->doQuery("INSERT INTO  `acl_users_actions` (`id_action`, `id_user`) VALUES ". $sValues);
            }

            $sValues = '';

            foreach($_POST['menu'] as $nMenu => $bChecked)
                if($bChecked !== null && $bChecked !== 0)
                    $sValues .= "('{$nMenu}', '{$_POST['id']}'),";

            if(!empty($sValues)) {
                $sValues[strlen($sValues)-1] = ';';

                Database::instance()->doQuery("INSERT INTO  `acl_users_menu` (`id_menu`, `id_user`) VALUES ". $sValues);
            }

            echo json_encode(array('success' => Database::instance()->tryCommit()));

            break;
    }

    return;
}

?>