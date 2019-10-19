<?php
/**
 *
 * IceCode - The Next Web Expression
 *
 * @category   	Core
 * @package    	Database
 * @description	Abstract Layer for Management Database
 * @version    	0.7.0
 * @author		Daniele Contarino & Antonio Scalzo
 * @contact		http://www.danielecontarino.it
 *
 */
 
class Database {
	protected static $instance;
	private $_oMySQLIMan;
	private $_currentDatabase;
	private $_mResultSet;
	private $_mHystoricalResultSet;
	private $_nError;
	private $_bResultTransaction;

	//Singleton
	public static function instance(){
		if(Database::$instance == null ){
            if (isset($GLOBAL['IceCode_Database']))
                Database::$instance = $GLOBAL['IceCode_Database'];
            else 
                $GLOBAL['IceCode_Database'] = Database::$instance = new Database();
		}

		return Database::$instance;
	}

	private function __construct() {
		$this->_oMySQLIMan = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
		$this->_oMySQLIMan->set_charset("utf8");
		$this->_currentDatabase = DB_DATABASE;
		$this->_mHystoricalResultSet=array();
		$this->_nError = mysqli_connect_errno();
	}

	public function changeDatabase($sDbName = DB_DATABASE) {
		$sOldDb = Database::instance()->getCurrentDatabase();
		$this->_oMySQLIMan->select_db($sDbName); 
		$this->_currentDatabase = $sDbName;
		return $sOldDb;
	}
	
	public function getCurrentDatabase(){return $this->_currentDatabase;}
	
	public function escapeString($sString) { return $this->_oMySQLIMan->real_escape_string($sString); }
	
	public function fetchAllRows($sSqlQuery, $isArrayMode = false, $sDatabase = false) {
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		
	
		if($isArrayMode){
			if($result = $this->_oMySQLIMan->query($sSqlQuery)) {
				$aaData = array();
				while ($mLine = $result->fetch_assoc()) $aaData[] = $mLine;
								
			} else 	$aaData = false;

		}else{
			if($this->_oMySQLIMan->real_query($sSqlQuery)) $aaData = new Database_Result($this->_oMySQLIMan);
			else $aaData =  false;
		}
		
		
		if($sDatabase) Database::instance()->changeDatabase($sOldDb);
		return $aaData;
	}
	
	public function fetchOneRow($sSqlQuery, $sDatabase = false) {
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		if($result = $this->_oMySQLIMan->query($sSqlQuery)) $mLine = $result->fetch_assoc();
		else $mLine = false;

		if($sDatabase) Database::instance()->changeDatabase($sOldDb);
		return $mLine;
	}

	public function fetchOneValue($sSqlQuery, $sDatabase = false) {
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		if($result = $this->_oMySQLIMan->query($sSqlQuery)) {
			$mLines = $result->fetch_row();
			$mLine = $mLines[0];
						
		} else $mLine = false;

		if($sDatabase) Database::instance()->changeDatabase($sOldDb);
		return $mLine;
	}
		
	public function doRealQuery($sSqlQuery, $sDatabase = false) { 
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		
	
		$bResult = $this->_oMySQLIMan->real_query($sSqlQuery);
		$this->_bResultTransaction = $this->_bResultTransaction && ($bResult != false);

		if($sDatabase) Database::instance()->changeDatabase($sOldDb);

		return  $bResult; 
	}

	public function doQuery($sSqlQuery, $sDatabase = false) { 
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		$bResult = $this->_oMySQLIMan->query($sSqlQuery);
		$this->_bResultTransaction = $this->_bResultTransaction && ($bResult != false);

		if($sDatabase) Database::instance()->changeDatabase($sOldDb);

		return  $bResult; 
	}

	public function doMultiQuery($sSqlQuery, $sDatabase = false) { 
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		$bResult = $this->_oMySQLIMan->multi_query($sSqlQuery);
		$this->_bResultTransaction = $this->_bResultTransaction && $bResult;

		if($bResult){
			$mResultSet = array();
			do {
				if ($result = $this->_oMySQLIMan->store_result())  $mResultSet[] = $result;
			} while ($this->_oMySQLIMan->next_result());
			
			if($sDatabase) Database::instance()->changeDatabase($sOldDb);

			return  $mResultSet; 

		}else return false;	
	}

	public function doInsertIntoQuery($sSqlQuery, $sDatabase = false) { 
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		$bResult = $this->_oMySQLIMan->query($sSqlQuery);
		$this->_bResultTransaction = $bResult && $this->_bResultTransaction;
		
		if($sDatabase) Database::instance()->changeDatabase($sOldDb);

		return ($bResult) ? $this->_oMySQLIMan->insert_id : false;
	}

	public function numRows($sSqlQuery, $sDatabase = false) { 
		//Se ho intensione di effettura la query su un'altro db, faccio lo swicth temporaneo del db
		if($sDatabase) $sOldDb = Database::instance()->changeDatabase($sDatabase);		

		$aaResult = $this->_oMySQLIMan->query($sSqlQuery);
		
		if($sDatabase) Database::instance()->changeDatabase($sOldDb);
		
		return ( $aaResult != false)? $aaResult->num_rows : -1; 
	}

	public function getEnumSetValues($sTable, $sField, $sDatabase = false) {
		$sEnumValues = $this->fetchOneRow("SHOW COLUMNS FROM `$sTable` LIKE '$sField';");
		if(preg_match_all("/'([^\']*)'/", $sEnumValues['Type'], $regs))  return $regs[1];
		else return null;
	}

	//Atomic Execution	
	public function startTransaction(){
		$this->_oMySQLIMan->autocommit(false);
		$this->_bResultTransaction = true;
	}

	public function tryCommit(){
		if($this->_bResultTransaction) $this->_oMySQLIMan->commit();
		else $this->_oMySQLIMan->rollback();
		$this->_oMySQLIMan->autocommit(true);

		return $this->_bResultTransaction;
	}
	
	public function rollback(){
		$this->_oMySQLIMan->rollback();
		$this->_oMySQLIMan->autocommit(true);
	}

	public function error() { return $this->_oMySQLIMan->error; }

	public function close() { $this->_oMySQLIMan->close(); }
	
	public function __destruct() { $this->_oMySQLIMan->close(); }
}

class Database_Result extends MySQLi_Result implements Countable, IteratorAggregate{
	/* Countable interface */
	public function count(){return $this->num_rows;}
	
	/* IteratorAggregate interface */
	public function getIterator(){return new Database_ResultIterator($this); }
}

class Database_ResultIterator implements Countable, SeekableIterator{
	/**
	 * Basic iterator for MySQLi_Result objects
	 *
	 * @author Werner Segers <werner@procurios.nl>
	 */
	protected $Result;
	protected $fetchMode;
	protected $position;
	protected $currentRow;

	/**
	 * Constructor
	 * @param MySQLi_Result $Result
	 * @param constant $fetchMode (MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH)
	 */
	public function __construct($Result, $fetchMode = MYSQLI_ASSOC){
		$this->Result = $Result;
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Destructor
	 * Frees the Result object
	 */
	public function __destruct(){ $this->Result->free(); }


	/* Countable interface */

	/**
	 * Returns the amount of rows in the result
	 * @return int
	 */
	public function count(){ return $this->Result->num_rows; }


	/* SeekableIterator interface */

	/**
	 * Rewinds the internal pointer
	 */
	public function rewind(){
		// data_seek moves the Results internal pointer
		$this->Result->data_seek($this->position = 0);

		// prefetch the current row to have it available for calls to current()
		// note that this advances the Results internal pointer.
		$this->currentRow = $this->Result->fetch_array($this->fetchMode);
	}

	/**
	 * Moves the internal pointer one step forward
	 */
	public function next(){
		++$this->position;

		// prefetch the current row to have it available for calls to current()
		$this->currentRow = $this->Result->fetch_array($this->fetchMode);
	}

	/**
	 * Moves the internal pointer to the specified offset
	 * @param int $offset
	 */
	public function seek($offset){
		if ($offset < $this->Result->num_rows) {
			$this->position = $offset;
			$this->Result->data_seek($offset);
			$this->currentRow = $this->Result->fetch_array($this->fetchMode);
		} else 	throw new OutOfBoundsException('Invalid seek position');
	}

	/**
	 * Returns true if the current position is valid, false otherwise.
	 * @return bool
	 */
	public function valid(){return $this->position < $this->Result->num_rows; }

	/**
	 * Returns the row that matches the current position
	 * @return array
	 */
	public function current(){ return $this->currentRow; }

	/**
	 * Returns the current position
	 * @return int
	 */
	public function key(){ return $this->position; }
}



