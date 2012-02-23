<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stefan
 * Date: 12.02.12
 * Time: 00:16
 * To change this template use File | Settings | File Templates.
 */
class Logger
{
	/**
	 * Options array for the PEAR::Log
	 * @var array
	 * @access private
	 * @since 0.1
	 */
	private $_options = array(
		'mode' => '0660',
		'persistent' => true
	);
	/**
	 * PEAR::Log-Instance
	 * @var object
	 * @access private
	 * @since 0.1
	 */
	private $_log = NULL;
	/**
	 * Construction of the object
	 * @param array $options
	 * @access public
	 * @uses PEAR::Log
	 * @since 0.1
	 * @return void
	 */
	public function _construct($options) {
		if (isset($options['filename']) || empty($options['filename'])) {
			$tmpdir = defined('TMP') && is_writeable(TMP) ? TMP : ini_get('upload_tmp_dir');
			if (!is_writeable($tmpdir)) {
				die(__CLASS__.': The Logfile-Dir are not writeable for this script. Dir: '.$tmpdir);
			}
			$this->_options['filename'] = $tmpdir.DS.'log.db';
		} else if (count($options) > 0) {
			if (!isset($options['filename']) || empty($options['filename']) || !is_writeable($options['filename'])) {
				die(__CLASS__.': The Logfile-Dir are not writeable for this script. Dir: '.$options['filename']);
			}
			$this->_options = array_merge($this->_options, $options);
		}
		if (is_null($this->_log)) {
			include_once(CLASSES.DS.'Log_Sqlite3.php');
			$this->_log = Log::factory('Sqlite3', 'logs', 'ident', $this->_options);
		}
	}
	/**
	 * Log-Methode
	 * This methode logs the $message into the Logtable of the SQLite-Instance
	 * optionally with the Logevent $event.
	 *
	 * @param string $message
	 * @param constant $event
	 * @access public
	 * @since 0.1
	 * @uses PEAR::Log()->log()
	 * @return void
	 */
	public function err($message, $event=PEAR_LOG_ERR) {
		$this->_log->log($message, $event);
	}
	/**
	 * This methode reads the log-table and if messages are available, this
	 * will return it as an array, otherwise this returned null
	 *
	 * @access public
	 * @since 0.1
	 * @uses
	 * @return mixed
	 */
	public function readLog() {
		if (!isset($this->_options['filename']) || empty($this->_options['filename']))
			return;
		$db = new SQLite3($this->_options['filename']);
		if ($db) {
			$rows=array();
			$sql = $db->query("SELECT * FROM log_table");
			while(($row=$sql->fetchObject())!==FALSE) {
				$rows[] = $row;
			}
			return $rows;
		} else {
			return $db->lastErrorMsg();
		}
	}
	/**
	 * Reads the sum of entries into the log-db
	 *
	 * @access public
	 * @since 0.1
	 * @uses
	 * @return int
	 */
	public function sumEntries() {
		if (!isset($this->_options['filename']) || empty($this->_options['filename']))
			return;
		$db = new SQLite3($this->_options['filename']);
		if ($db) {
			$sql = $db->query("SELECT SUM(id) AS sum FROM log_table");
			$row = $sql->fetchObject();
			return (isset($row->sum)) ? (int)$row->sum : 0;
		} else {
			return $db->lastErrorMsg();
		}
	}
}
