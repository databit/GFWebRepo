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
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

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
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->store($_FILES['upload']['tmp_name'], $_FILES['upload']['name']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'rename':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->rename($_POST['from'], $_POST['to']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'delete':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->delete($_POST['filename']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'mkdir':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->mkdir($_POST['name']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'get':
            break;

        case 'read':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $mBuffer = '';
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->read($_POST['filename'], $mBuffer);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'buffer' => $mBuffer, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

        case 'write':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['gfWebRepo']['user']['username'], $_SESSION['gfWebRepo']['user']['password']);        

            if($_mHandler->isConnected()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->write($_POST['filename'], $_POST['buffer']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false)));
            
            break;

    }

    return;
}

?>