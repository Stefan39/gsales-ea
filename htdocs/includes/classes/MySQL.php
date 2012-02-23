<?php
/**
 * This class creates a MySQL-Singleton Object using PEAR::MDB2 for actions
 * to MySQL-Storages
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.1
 * @copyright 2012 Momo Net GmbH
 */
include_once('MDB2.php');
class MySQL
{
	/**
	 * Class obejct
	 *
	 * @staticvar object
	 * @access private
	 */
	static private $_object = NULL;
	/**
	 * Instantiate PEAR::MDB2 object
	 *
	 * @see http://pear.php.net/manual/en/package.database.mdb2.intro-connect.php
	 * @var array
	 * @access private
	 */
	private $_mdb = array();
	/**
	 * DSN
	 * @var array
	 * @access private
	 */
	private $_dsn = array();
	/**
	 * MDB2-Options
	 * @var array
	 * @access private
	 */
	private $_options = array();
	/**
	 * Private construction of the object. To initialize this object, please
	 * use ::getObject($params=array()) Function.
	 *
	 * @params array $data
	 * @access private
	 * @return void
	 */
	private function __construct(array $data) {
		$options = array_merge(array(
				'ssl' => false,
				'persistent' => false,
				'debug' => 2,
				'portability' => MDB2_PORTABILITY_ALL
			),
			(isset($data['options']) && is_array($data['options'])) ? $data['options'] : array()
		);
		$dsn = array_merge(array(
				'phptype' => 'mysql',
				'hostspec' => 'localhost',
				'dbname' => 'mysql'
			),
			(isset($data['dsn']) && is_array($data['dsn'])) ? $data['dsn'] : array()
		);
		$this->_dsn = $dsn;
		$this->_options = $options;
	}
	/**
	 * Forbid cloning
	 *
	 * @access private
	 * @return void
	 */
	private function __clone() {}
	/**
	 * Instantiate a singleton Object from this class
	 *
	 * @param array $data
	 * @access public
	 * @static
	 * @since 0.1
	 * @return object MySQL
	 */
	static public function getObject($data=array()) {
		if (is_null(self::$_object))
			self::$_object = new self($data);
		return self::$_object;
	}
	/**
	 * This Methode returned an instanstiate PEAR::MDB2 Object.
	 *
	 * @access public
	 * @since 0.1
	 * @return object PEAR::MDB2
	 */
	public function getDb() {
		$this->_initMdb();
		return $this->_mdb;
	}
	/**
	 * Test Database Connection
	 * @param array $dsn
	 * @access public
	 * @return boolean
	 */
	public function testDb(array $dsn) {
		$options = array(
			'ssl' => false,
			'persistent' => false,
			'debug' => 2,
			'portability' => MDB2_PORTABILITY_ALL
		);
		$this->_initMdb($dsn, $options);
		if (PEAR::isError($this->_mdb)) {
			return $this->_mdb->getDebugInfo();
		}
		return true;
	}
	/**
	 * Instantiate MDB2
	 * @access private
	 * @return object MDB2
	 */
	private function _initMdb() {
		$this->_mdb = MDB2::singleton($this->_dsn, $this->_options);
		$this->_mdb->setFetchMode(MDB2_FETCHMODE_ASSOC);
	}
}
