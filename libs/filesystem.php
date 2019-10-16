<?php
/**
 *
 * IceCode - The Next Web Expression
 *
 * @category   	Core
 * @package    	FileSystem
 * @description	Secure File Management
 * @version    	0.7.0
 * @author		Daniele Contarino
 * @contact		http://www.danielecontarino.it
 *
 */

if(WRITE_MODE == "ftp") 
    include_once 'ftp.php';

class FileSystem{

	protected static $_mHandler = false;

	protected static function open(){
		switch(WRITE_MODE){
			case "ftp": 
				$_mHandler = IceFTP::getInstance(FTP_HOST, FTP_PORT, null, FTP_USER, FTP_PASS); 
				$_mHandler->chdir(realpath(DEEP));
				break;
		}
	}
	
	public  static function read($sFile){
		$sBuffer = '';
		switch(WRITE_MODE){
			case "php":
				$_mHandler = fopen($sFile, 'r'); 
				while (!feof($_mHandler)) $sBuffer .= fread($_mHandler, 8192);
				fclose($_mHandler);
				break;
				
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				$_mHandler->read($sFile, $sBuffer);
				break;
		}
		
		return $sBuffer;
	}

	public static function write($sFile, $sBuffer, $bIsAppend = false){
		switch(WRITE_MODE){
			case "php": 
				$_mHandler = fopen($sFile, ($bIsAppend)? 'a+' : 'w+'); 
				$bResult = fwrite($_mHandler, $sBuffer);
				fclose($_mHandler);
				break;
				
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				if($bIsAppend) {
					$sPreviousContent = '';
					$_mHandler->read($sFile, $sPreviousContent);
					$sBuffer = $sPreviousContent . $sBuffer;
				}
				
				$bResult = $_mHandler->write($sFile, $sBuffer);
				$_mHandler->chmod($sFile, 0775);
				break;
				
			default:
				$bResult = false;
			}

			return $bResult;
			
	}

	public static function listDirectories($sPath = '.'){
		switch(WRITE_MODE){
			case "php": 
				$aDirectories = array();
				$mResource=opendir($sPath);
				if(!$mResource) return false;	

				while ($sDirectory = readdir($mResource)) 
					if($sDirectory != '..' && $sDirectory !='.' && $sDirectory !='' && is_dir($sPath.'/'.$sDirectory)) $aDirectories[]=$sDirectory;

				closedir($mResource);
				clearstatcache();

				sort($aDirectories);
				return $aDirectories;
			
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				return $_mHandler->listDetails($sPath, 'folders');
				
			default: return false;
		}
	}

	public static function listFile($sPath){
		switch(WRITE_MODE){
			case "php":
				$aFiles = array();
				$mResource=opendir($sPath);
				if(!$mResource) return false;	

				while ($sFile = readdir($mResource)) 
					if($sFile != '..' && $sFile !='.' && $sFile !='' && !is_dir($sPath.'/'.$sFile)) $aFiles[]=$sFile;

				closedir($mResource);
				clearstatcache();

				sort($aFiles);
				return $aFiles;

			case "ftp":
				if(!$_mHandler) FileSystem::open();
				return $_mHandler->listDetails($sPath, 'files');

			default: return false;
		}
	}


	public static function chmod($sPath, $nMode){
		switch(WRITE_MODE){
			case "php": return chmod($sPath, $nMode);
			case "ftp": 
				if(!$_mHandler) FileSystem::open();
				return $_mHandler->chmod($sPath, $nMode);
			
			default: return false;
		}
				
	}

	public static function mkdir($sPath, $nMode = 0775){
		switch(WRITE_MODE){
			case "php": return mkdir($sPath, $nMode);
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				$bResult = $_mHandler->mkdir($sPath);
				$_mHandler->chmod($sPath, $nMode);
				return $bResult;

			default: return false;
		}
	}

	public static function delete($sPath){
		switch(WRITE_MODE){
			case "php": 
				$bResult = true;
				if (!is_dir($sPath)) return unlink($sPath);
				else {
					$mResource=opendir($sPath);
					if(!$mResource) return false;	

					while ($sFile = readdir($mResource)) 
						if($sFile != '..' && $sFile !='.' && $sFile !='')
			 				$bResult = $bResult && FileSystem::delete($sPath.'/'.$sFile);

					closedir($mResource);
					clearstatcache();
					return $bResult && rmdir($sPath);
				}

			case "ftp":
				if(!$_mHandler) FileSystem::open();
				return $_mHandler->delete($sPath);

			default: return false;
		}
	}

	public static function rename($sFrom, $sTo){
		switch(WRITE_MODE){
			case "php": return rename($sFrom, $sTo);
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				return $_mHandler->rename($sFrom, $sTo);
	
			default: return false;
		}
	}

	public static function copy($sFrom, $sTo){
		switch(WRITE_MODE){
			case "php": return copy($sFrom, $sTo);
			case "ftp":
				if(!$_mHandler) FileSystem::open();
				$sBuffer = '';
				$_mHandler->read($sFrom, $sBuffer);
				if(!empty($sBuffer)){
					$bResult = $_mHandler->write($sTo, $sBuffer);
					$_mHandler->chmod($sTo, 0775);
					return $bResult;
				}else
					return false;
 
			default: return false;
		}
	}

}

