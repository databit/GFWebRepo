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
                    $bSuccess = $bSuccess && Database::instance()->doQuery("CREATE USER IF NOT EXISTS '{$_POST['user']}'@'localhost' IDENTIFIED WITH mysql_native_password BY '{$_POST['password']}';");

                    for($i=1; $i<=5;$i++){
                        $bSuccess = $bSuccess && Database::instance()->doQuery("CREATE DATABASE IF NOT EXISTS `stud_{$_POST['user']}_{$i}`;");
                        $bSuccess = $bSuccess && Database::instance()->doQuery("GRANT ALL PRIVILEGES ON `stud_{$_POST['user']}_{$i}`.* TO '{$_POST['user']}'@'localhost';");
                    }

                    // Create personal folder on webserver
                    if($bSuccess){
                        $nRetvalMkDir = $nRetvalCHMOD = 0;
                        system('mkdir '. FTP_ROOT_PATH . $_POST['user'], $nRetvalMkDir);

                        if($nRetvalMkDir == 1) {
                            system('chmod 775 '. FTP_ROOT_PATH . $_POST['user'], $nRetvalCHMOD);
                            if($nRetvalCHMOD == 1)
                                // Committo tutta la transazione e verifico la correttezza di tutti i dati
                                die(json_encode(array('success' => Database::instance()->tryCommit(), 'error' => 'sql-error')));
                            
                            else {
                                // Se ho creato la cartella e qualcosa è andato storto la rimuovo
                                system('rm -r '. FTP_ROOT_PATH . $_POST['user'], $nRetvalMkDir);
                                // Annullo tutte le modifiche
                                Database::instance()->rollback();
                                die(json_encode(array('success' => false, 'error' => 'file-system-error-chmod')));
                            }

                        } else {
                            // Annullo tutte le modifiche
                            Database::instance()->rollback();
                            die(json_encode(array('success' => false, 'error' => 'file-system-error-mkdir')));
                        }
                    } else { 
                        // Annullo tutte le modifiche
                        Database::instance()->rollback();
                        die(json_encode(array('success' => $bSuccess, 'error' => 'db-error')));
                     }
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
            // Avvio la transazione
            Database::instance()->startTransaction();

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
                        
            if(!empty($_POST['password'])){
                Database::instance()->doQuery("ALTER USER '". Database::instance()->escapeString($_POST['user']) ."'@'localhost' IDENTIFIED BY '{$_POST['password']}';");
                Database::instance()->doQuery("FLUSH PRIVILEGES;");
            }
            
            die(json_encode(array('success' => Database::instance()->tryCommit())));
            break;

        case 'delete-user':
            if(isset($_POST['deleteFiles']) && $_POST['deleteFiles']){
                // Avvio la transazione
                Database::instance()->startTransaction();

                $bSuccess = Database::instance()->doQuery("DELETE FROM `". PURE_FTP_TABLE ."` WHERE `user` = '". Database::instance()->escapeString($_POST['user']) ."';");

                // Delete DB profile e database
                $bSuccess = $bSuccess && Database::instance()->doQuery("DROP USER '{$_POST['user']}'@'localhost';");

                for($i=1; $i<=5;$i++)
                    $bSuccess = $bSuccess && Database::instance()->doQuery("DROP DATABASE `stud_{$_POST['user']}_{$i}`;");
                

                // drop personal folder on webserver
                if($bSuccess){
                    system('rm -r '. FTP_ROOT_PATH . $_POST['user'], $nRetvalRM);

                    if($nRetvalRM == 1){
                        // Committo tutta la transazione e verifico la correttezza di tutti i dati
                        $bSuccess = Database::instance()->tryCommit();
                        die(json_encode(array('success' => $bSuccess, 'error' => 'sql-error')));

                    } else {
                        // Annullo tutte le modifiche
                        Database::instance()->rollback();
                        $bSuccess = false;
                     } 

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
            else {
                // Avvio la transazione
                Database::instance()->startTransaction();
                // Cambio la password dell'utente FTP
                Database::instance()->doQuery("UPDATE `". PURE_FTP_TABLE ."` SET `password` = '". crypt($_POST['user'], FTP_CRYPT_SALT) ."' WHERE `user` = '". Database::instance()->escapeString($_POST['user']) ."';");
                // Cambio la password dell'utente del database MySQL e aggiorno i permessi
                Database::instance()->doQuery("ALTER USER '". Database::instance()->escapeString($_POST['user']) ."'@'localhost' IDENTIFIED BY '{$_POST['password']}';");
                Database::instance()->doQuery("FLUSH PRIVILEGES;");
                // Committo i dati sul server e invio il risultato
                die(json_encode(array('success' => Database::instance()->tryCommit())));
            }
            break;
    }
    return;
}
?>
