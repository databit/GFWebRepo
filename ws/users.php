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
            $sQuery="   SELECT `user`, `name`, `email`, `status`, `is_admin`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files` 
                        FROM `". PURE_FTP_TABLE ."`
                        WHERE `deleted` = '0';";

            die(json_encode(Database::instance()->fetchAllRows($sQuery, true)));
            break;

        case 'search':
            $sQuery = " SELECT `user`, `name`, `email`, `status`, `is_admin`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files` 
                        FROM ". PURE_FTP_TABLE ." 
                        WHERE `user` LIKE '%". $_POST['keyword'] ."%'
                        WHERE `deleted` = '0'
                        LIMIT 12;";

            echo json_encode(Database::instance()->fetchAllRows($sQuery, true));
            break;
            
        case 'add-user':
            if(strlen($_POST['password']) < 8)
                die(json_encode(array('success' => false, 'error' => 'password-too-short')));

            else {
                $sQuery="   SELECT `deleted`
                            FROM `". PURE_FTP_TABLE ."`
                            WHERE `user` = '". Database::instance()->escapeString($_POST['user'])."'
                            LIMIT 1 ;";

                if (Database::instance()->numRows($sQuery) > 0){
                    if(Database::instance()->fetchOneValue($sQuery) === 1){
                        $bSuccess = Database::instance()->doQuery(" REPLACE INTO `". PURE_FTP_TABLE ."` (`user`, `name`, `email`, `status`, `password`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files`, `deleted`)
                                                                    VALUES (
                                                                    '". Database::instance()->escapeString($_POST['user']) ."', 
                                                                    '". Database::instance()->escapeString($_POST['name']) ."', 
                                                                    '". Database::instance()->escapeString($_POST['email']) ."', 
                                                                    '1', 
                                                                    '". crypt($_POST['password'], FTP_CRYPT_SALT) ."',
                                                                    '{$_POST['uid']}',
                                                                    '{$_POST['gid']}',
                                                                    '". FTP_ROOT_PATH . $_POST['user'] ."',
                                                                    '{$_POST['ul_bandwidth']}',
                                                                    '{$_POST['dl_bandwidth']}',
                                                                    '". Database::instance()->escapeString($_POST['comment']) ."',
                                                                    '". Database::instance()->escapeString($_POST['ipaccess']) ."',
                                                                    '{$_POST['quota_size']}',
                                                                    '{$_POST['quota_files']}',
                                                                    '0');");                    

                        die(json_encode(array('success' => $bSuccess, 'warning' => 'username-restored')));
                    
                    } else 
                        die(json_encode(array('success' => false, 'error' => 'username-alredy-taken')));
            
                } else {
                    // Avvio la transazione
                    Database::instance()->startTransaction();

                    $bSuccess = Database::instance()->doQuery(" INSERT INTO `". PURE_FTP_TABLE ."` (`user`, `name`, `email`, `status`, `password`, `uid`, `gid`, `dir`, `ul_bandwidth`, `dl_bandwidth`, `comment`, `ipaccess`, `quota_size`, `quota_files`)
                                                                VALUES (
                                                                '". Database::instance()->escapeString($_POST['user']) ."', 
                                                                '". Database::instance()->escapeString($_POST['name']) ."', 
                                                                '". Database::instance()->escapeString($_POST['email']) ."', 
                                                                '1', 
                                                                '". crypt($_POST['password'], FTP_CRYPT_SALT) ."',
                                                                '{$_POST['uid']}',
                                                                '{$_POST['gid']}',
                                                                '". FTP_ROOT_PATH . $_POST['user'] ."',
                                                                '{$_POST['ul_bandwidth']}',
                                                                '{$_POST['dl_bandwidth']}',
                                                                '". Database::instance()->escapeString($_POST['comment']) ."',
                                                                '". Database::instance()->escapeString($_POST['ipaccess']) ."',
                                                                '{$_POST['quota_size']}',
                                                                '{$_POST['quota_files']}');");

                    // Create DB profile e database
                    /*$bSuccess = $bSuccess && Database::instance()->doQuery("CREATE USER '{$_POST['User']}'@'localhost' IDENTIFIED WITH mysql_native_password AS '{$_POST['password']}';GRANT USAGE ON *.* TO '{$_POST['User']}'@'localhost' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");

                    for($i=1; $i<=5;$i++){
                        $bSuccess = $bSuccess && Database::instance()->doQuery("CREATE DATABASE `{$_POST['User']}_{$i}`;");
                        $bSuccess = $bSuccess && Database::instance()->doQuery("GRANT ALL PRIVILEGES ON `{$_POST['User']}_{$i}`.* TO '{$_POST['User']}'@'localhost';");
                    }*/

                    // Create personal folder on webserver
                    if($bSuccess){
                        //system('mkdir '. FTP_ROOT_PATH . $_POST['User'], $retval);
                        //system('chgrp '. FTP_GROUP_ID .' '. FTP_ROOT_PATH . $_POST['User'], $retval);
                        //system('chmod 775 '. FTP_ROOT_PATH . $_POST['User'], $retval);

                        // Committo tutta la transazione e verifico la correttezza di tutti i dati
                        $bSuccess = Database::instance()->tryCommit();
                        die(json_encode(array('success' => $bSuccess, 'error' => 'sql-error')));

                    } else { 
                        // Annullo tutte le modifiche
                        Database::instance()->rollback();
                        $bSuccess = false;
                     }

                    die(json_encode(array('success' => $bSuccess, 'error' => 'system-error')));
                }
            }
            break;

        case 'edit-user':
            if(!empty($_POST['password'])) { 
                if(strlen($_POST['password']) < 8)
                    die(json_encode(array('success' => false, 'error' => 'password-too-short')));

                else if($_POST['user'] == $_POST['password'])
                        die(json_encode(array('success' => false, 'error' => 'no-default-password')));
            }
            $sQuery = " UPDATE `". PURE_FTP_TABLE ."`
                        SET ". (!empty($_POST['name']) ? "`name` = '". Database::instance()->escapeString($_POST['name']) ."'," : '') ."
                            ". (!empty($_POST['email']) ? "`email` = '". Database::instance()->escapeString($_POST['email']) ."'," : '') ."
                            ". (!empty($_POST['status']) ? "`status` = '". ($_POST['status'] ? '1': '0') ."'," : '') ."
                            ". (!empty($_POST['password']) ? "`password` = '". crypt($_POST['password'], FTP_CRYPT_SALT) ."'," : '') ."
                            ". (!empty($_POST['uid']) ? "`uid` = '{$_POST['uid']}'," : '') ."
                            ". (!empty($_POST['gid']) ? "`gid` = '{$_POST['gid']}'," : '') ."
                            ". (!empty($_POST['ul_bandwidth']) ? "`ul_bandwidth` = '{$_POST['ul_bandwidth']}'," : '') ."
                            ". (!empty($_POST['dl_bandwidth']) ? "`dl_bandwidth` = '{$_POST['dl_bandwidth']}'," : '') ."
                            ". (!empty($_POST['ipaccess']) ? "`ipaccess` = '". Database::instance()->escapeString($_POST['ipaccess']) ."'," : '') ."
                            ". (!empty($_POST['quota_size']) ? "`quota_size` = '{$_POST['quota_size']}'," : '') ."
                            ". (!empty($_POST['quota_files']) ? "`quota_files` = '{$_POST['quota_files']}'," : '') ."
                            ". (!empty($_POST['comment']) ? "`comment` = '". Database::instance()->escapeString($_POST['comment']) ."'," : '') ."
                            `user` = '". Database::instance()->escapeString($_POST['user']) ."'
                        WHERE `user` = '". Database::instance()->escapeString($_POST['user']) ."';";
            die(json_encode(array('success' => Database::instance()->doQuery($sQuery))));
            break;

        case 'delete-user':
            if(isset($_POST['deleteFiles']) && $_POST['deleteFiles']){
                // Avvio la transazione
                Database::instance()->startTransaction();

                $bSuccess = Database::instance()->doQuery("DELETE FROM `". PURE_FTP_TABLE ."` WHERE `user` = '". Database::instance()->escapeString($_POST['user']) ."';");

                // Delete DB profile e database
                /*$bSuccess = $bSuccess && Database::instance()->doQuery("DROP USER '{$_POST['user']}'@'localhost';");

                for($i=1; $i<=5;$i++)
                    $bSuccess = $bSuccess && Database::instance()->doQuery("DROP DATABASE `{$_POST['user']}_{$i}`;");
                */

                // drop personal folder on webserver
                if($bSuccess){
                    //system('rm -r '. FTP_ROOT_PATH . $_POST['User'], $retval);

                    // Committo tutta la transazione e verifico la correttezza di tutti i dati
                    $bSuccess = Database::instance()->tryCommit();
                    die(json_encode(array('success' => $bSuccess, 'error' => 'sql-error')));

                } else { 
                    // Annullo tutte le modifiche
                    Database::instance()->rollback();
                    $bSuccess = false;
                 }

                die(json_encode(array('success' => $bSuccess, 'error' => 'system-error')));

            } else 
                die(json_encode(array('success' => Database::instance()->doQuery("UPDATE `". PURE_FTP_TABLE ."` SET `deleted` = '1'  WHERE `user` = '{$_POST['user']}';"))));

            break;

        case 'reset-password':
            if(empty($_POST['user']))
                die(json_encode(array('success' => false, 'error' => 'missing-user')));
            else 
                $sQuery = " UPDATE `". PURE_FTP_TABLE ."`
                            SET `password` = '". crypt($_POST['user'], FTP_CRYPT_SALT) ."'
                            WHERE `user` = '". Database::instance()->escapeString($_POST['user']) ."';";
            echo json_encode(array('success' => Database::instance()->doQuery($sQuery)));
            break;
    }
    return;
}
?>