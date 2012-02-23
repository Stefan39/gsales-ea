<?php
/**
 * New Log Handler for writing Logs into a sqlite3 Database.
 *
 * This handler using PEAR::Log and extended it. For this is it important,
 * that PEAR::Log are installed and correctly included before this handler
 * will be use.
 *
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 1.0
 * @uses PEAR::Log
 * @copyright 2012 Jacomeit.com
 */
class Log_Sqlite3 extends Log
{
	/**
	 * The SQLite Options
	 * @var array
	 * @access private
	 */
	private $_options = array('mode' => '0660',
							  'persistent' => FALSE
							 );
	/**
	 * SQLite DB-Objekt
	 * @var object
	 * @access private
	 */
	private $_db = NULL;
	/**
	 * Param for the existing connection or not.
	 * @param boolean
	 * @access private
	 */
	private $_existingConnection=FALSE;
	/**
	 * Tablename
	 * @var string
	 * @access private
	 */
	private $_tableName = 'log_table';
	/**
	 * Constructor of the class
	 * Set the properties of the class
	 *
	 * @param string $name		The target SQL Table
	 * @param string $ident		The identification field
	 * @param mixed $conf 		Can be an array of configuration options used
	 * 							to open a new database connection or an already
	 * 							opened sqlite connection
	 * @param int $level		Log messages up to and including this level
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct($name, $ident, $conf, $level = PEAR_LOG_DEBUG) {
		$this->_id = md5(microtime());
		$this->_tableName = $name;
		$this->_ident = $ident;
		$this->_mask = Log::UPTO($level);
		if (is_array($conf)) {
			foreach($conf as $k=>$opt) {
				$this->_options[$k]=$opt;
			}
		} else {
			$this->_db = $conf;
			$this->_existingConnection=TRUE;
		}
	}
	/**
	 * Opens a connection to the database, if it has not already been opened.
	 * This is implicity called by log(), if neccessary.
	 *
	 * @access public
	 * @since 1.0
	 * @return boolean
	 */
	public function open() {
		if (is_resource($this->_db)){
			$this->_opened=TRUE;
			return $this->_createTable();
		} else {
			$this->_db = new SQLite3($this->_options['filename']);
			$this->_createTable();
			$this->_opened=TRUE;
		}
		return $this->_opened;
	}
	/**
	 * Closes the connection to the database if it is still open and we were
	 * the ones that opened it. It is the caller's responsible to close an
	 * existing connection that was passed to us via $conf['db'].
	 *
	 * @access public
	 * @since 1.0
	 * @return boolean
	 */
	public function close() {
		if ($this->_existingConnection) {
			return FALSE;
		}
		if ($this->_opened) {
			$this->_opened=FALSE;
			$this->_db->close();
		}
		return ($this->_opened===FALSE);
	}
	/**
	 * Inserts the $message to the currently open database. Calls open(), if
	 * neccessary. Also passes the message along to any Log_observer instances
	 * that are observing this log.
	 *
	 * @param mixed $message	String or object containing the message to log
	 * @param string $priority 	The priority of the message. Valid values are:
	 * 							PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT,
	 * 							PEAR_LOG_ERR, PEAR_LOG_WARNING,
	 * 							PEAR_LOG_NOTICE, PEAR_LOG_INFO and
	 * 							PEAR_LOG_DEBUG.
	 * @access public
	 * @since 1.0
	 * @return boolean			True on success or false on failure
	 */
	public function log($message, $priority=NULL) {
		if (is_null($priority))
			$priority=$this->_priority;

		if(!$this->_isMasked($priority))
			return FALSE;

		if (!$this->_opened && !$this->open())
			return FALSE;

		$message = $this->_extractMessage($message);
		$q = sprintf('INSERT INTO [%s] (logtime, ident, priority, message) ' .
				"VALUES ('%s', '%s', %d, '%s')",
			$this->_table,
			strftime('%Y-%m-%d %H:%M:%S', time()),
			$this->_db->escapeString($this->_ident),
			$priority,
			$this->_db->escapeString($message));
		if(($sql=$this->_db->querySingle($q))===FALSE)
			return FALSE;
		$this->_announce(array('priority'=>$priority,'message'=>$message));
		return TRUE;
	}
	/**
	 * Checks whether the log table exists and creates it if necessary.
	 *
	 * @access private
	 * @since 1.0
	 * @return boolean  True on success or false on failure.
	 */
	private function _createTable()
	{
		$q = "SELECT name FROM sqlite_master WHERE name='{$this->_table}' AND type='table'";
		if (($sql=$this->_db->querySingle($q)) === FALSE) {
			return FALSE;
			die(__CLASS__.' SQLite-Error: '.$this->_db->lastErrorMsg());
		}
		if ($sql->columnCount() == 0) {
			$q = "CREATE TABLE ['{$this->_table}'] (
			  id INTEGER PRIMARY KEY NOT NULL,
			  logtime NOT NULL,
			  ident CHAR(16) NOT NULL,
			  priority INT NOT NULL,
			  message)";
			if (($cre=$this->_db->querySingle($q)) === FALSE) {
				return FALSE;
				die(__CLASS__.' SQLite-Error: '.$this->_db->lastErrorMsg());
			}
		}
		return TRUE;
	}
}
