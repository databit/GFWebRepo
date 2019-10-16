<?php
//If use AngularJS Ajax
if(empty($_POST))
    $_POST = json_decode(file_get_contents('php://input'), true);

//Starting PHP session
session_start();

require_once '../config.php';
include_once '../libs/database.php';
include_once '../libs/ftp.php';

if(isset($_POST['cmd'])){
    switch($_POST['cmd']){
        case 'list':
            $_mHandler = IceFTP::getInstance('127.0.0.1', '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : './';

                exit(json_encode(array(
                        'success' => true,
                        'folders' => $_mHandler->listDetails($sPath, 'folders'),
                        'files' => $_mHandler->listDetails($sPath, 'files'))
                    ));
                $_mHandler->quit();

            } else 
                exit(json_encode(array('success' => false)));

            break;

        case 'upload':
            $_mHandler = IceFTP::getInstance('127.0.0.1', '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : './';

                $_mHandler->chdir($sPath);
                if($_mHandler->store($_FILES['tmp_name']['name']))
                    $bResult = $_mHandler->rename($_FILES['tmp_name']['name'], $_FILES['upload']['name']);
                else
                    $bResult = false;
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult)));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'get':
            break;

        case 'read':
            break;

        case 'write':
            break;

    }

    return;
}

?>