<?php
//If use AngularJS Ajax
if(empty($_POST))
    $_POST = json_decode(file_get_contents('php://input'), true);

//Starting PHP session
session_start();

require_once '../config.php';
include_once '../libs/database.php';
include_once '../libs/ftp.php';

if(isset($_GET['cmd']) && $_GET['cmd']=='download')
    $_POST = $_GET;

if(isset($_POST['cmd'])){
    switch($_POST['cmd']){
        case 'list':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                exit(json_encode(array(
                        'success' => true,
                        'folders' => $_mHandler->listDetails($sPath, 'folders'),
                        'files' => $_mHandler->listDetails($sPath, 'files'))
                    ));
                $_mHandler->quit();

            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));

            break;

        case 'list-folders':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                exit(json_encode(array(
                        'success' => true,
                        'folders' => $_mHandler->listDetails($sPath, 'folders'))
                    ));
                $_mHandler->quit();

            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));

            break;

        case 'upload':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->store($_FILES['upload']['tmp_name'], $_FILES['upload']['name']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'rename':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->rename($_POST['from'], $_POST['to']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'delete':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->delete($_POST['filename']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'create':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_POST['isFile'] ? $_mHandler->create($_POST['filename']) : $_mHandler->mkdir($_POST['filename']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'move':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPathFrom = $_POST['pathFrom'];
                $sPathTo = $_POST['pathTo'];

                // Create the temporaly file for download file from ftp
                $sTempFile = tempnam(sys_get_temp_dir(), $_SESSION['webRepo']['user']['username']);

                // Copy the file localy
                $_mHandler->chdir($sPathFrom);
                if($_mHandler->get($sTempFile, $_POST['filename'])){
                    // Send the local file to new path 
                    $_mHandler->chdir($sPathTo);
                    if($_mHandler->store($sTempFile, $_POST['filename'])){
                        // Delete the old file
                        $_mHandler->chdir($sPathFrom);
                        if($_mHandler->delete($_POST['filename']))
                            $aResult = array('success' => true );
                        else 
                            $aResult = array('success' => false, 'is_logged' => true, 'error' => 'delete-failed' );

                    } else 
                        $aResult = array('success' => false, 'is_logged' => true, 'error' => 'store-failed' );
                        
                } else 
                    $aResult = array('success' => false, 'is_logged' => true, 'error' => 'get-failed' );

                $_mHandler->quit();
                exit(json_encode($aResult));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'download':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';
                
                // Create the temporaly file for download file from ftp
                $sTempFile = tempnam(sys_get_temp_dir(), $_SESSION['webRepo']['user']['username']);

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->get($sTempFile, $_POST['filename']);
                
                $_mHandler->quit();

                // Set the HTTP header params
                $sMimeType = mime_content_type($sTempFile);
                $nSize = filesize($sTempFile);

                header('Content-Description: File Transfer');
                header('Content-Type: '. $sMimeType);
                header('Content-Disposition: attachment; filename='. $_POST['filename']); 
                header('Content-Transfer-Encoding: binary');
                header('Connection: Keep-Alive');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . $nSize);

                // Read the file and stream out on browser
                $mHandle = fopen($sTempFile, 'rb');
                while (!feof($mHandle)) {
                    $sBuffer = fread($mHandle, 1024*1024);
                    echo $sBuffer;
                    ob_flush();
                    flush();
                }

                fclose($mHandle);

                exit(0);
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'read':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $mBuffer = '';
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->read($_POST['filename'], $mBuffer);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'buffer' => $mBuffer, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

        case 'write':
            $_mHandler = FTP::getInstance(FTP_HOST, '21', null, $_SESSION['webRepo']['user']['username'], $_SESSION['webRepo']['user']['password']);        

            if($_mHandler->isConnected() && $_mHandler->isLogged()){
                $sPath = isset($_POST['path']) ? $_POST['path'] : '/';

                $_mHandler->chdir($sPath);
                $bResult = $_mHandler->write($_POST['filename'], $_POST['buffer']);
                
                $_mHandler->quit();
                exit(json_encode(array('success' => $bResult, 'is_logged' => true, 'error' => $bResult ? $_mHandler->getLastError() : '' )));
            
            } else 
                exit(json_encode(array('success' => false, 'is_logged' => $_mHandler->isLogged())));
            
            break;

    }

    return;
}

?>