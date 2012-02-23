<?php
/**
 * Authentification class
 * This class handles the authentifications and login users.
 *
 * @package gsales-ea
 * @version 1.0
 * @author	Stefan Jacomeit
 * @copyright 2012 Jacomeit.com
 */
class Authentification
{
	/**
	 * Class object
	 * @staticvar object
	 * @access private
	 */
	static private $_object = null;
	/**
	 * Auth-Container with the informations over the current user
	 * @var array
	 * @access private
	 */
	private $_container = array();
	/**
	 * PEAR::MDB2 instance injected by the instantiating of this class
	 * @var object
	 * @access private
	 */
	private $_mdb = null;
	/**
	 * Forbid cloning
	 * @access private
	 */
	private function __clone() {}
	/**
	 * Statically method to initialize the authentification object
	 * @param object $mdb	MySQL instance
	 * @access public
	 * @static
	 * @since 0.1
	 * @return object
	 */
	static public function init(MySQL $mdb) {
		if (is_null(self::$_object))
			self::$_object = new self($mdb);
		return self::$_object;
	}
	/**
	 * Returned the complete User-Information from current user
	 * @access public
	 * @return array
	 */
	public function getInfos() {
		return $this->_container;
	}
	/**
	 * Returned the username of the current user
	 * @access public
	 * @return string
	 */
	public function getUsername() {
		return $this->_container['user'];
	}
	/**
	 * Returned the UserID of the current user
	 * @access public
	 * @return integer
	 */
	public function getId() {
		return (int)$this->_container['id'];
	}
	/**
	 * Returned the group of the current user, if exists. otherwise return it
	 * null
	 * @access public
	 * @return mixed
	 */
	public function getGroup() {
		return isset($this->_container['grp']) ? $this->_container['grp'] : null;
	}
	/**
	 * Checks, if the current user is into the group $grp
	 * @param string $grp
	 * @access public
	 * @return bool
	 */
	public function isInGroup($grp) {
		return (isset($this->_container['grp']) && ($this->_container['grp']==$grp)) ? true : false;
	}
	/**
	 * The private constructor of the class
	 * Gets the authentification informations from the global $_SERVER
	 * variables and save it into the class container
	 * @param object $mdb 	MySQL instance
	 * @access private
	 * @since 0.1
	 * @return void
	 */
	private function __construct($mdb) {
		// AS first, starts a session
		session_start();

		$this->_mdb = $mdb;
		$this->_container['user'] = $_SERVER['PHP_AUTH_USER'];
		// Authenticate the current user
		$this->_authenticate();
	}
	/**
	 * Private method to authenticate the current user with the users-table
	 * over a mdb2-instance
	 * @access private
	 * @since 0.1
	 * @return mixed	Returned the ID (Integer) from current user or false
	 */
	private function _authenticate() {
		if (!isset($_SESSION['_auth'])) {
			$id = false;
			$db = $this->_mdb->getDb();
			$row = $db->queryRow(sprintf("SELECT * FROM users WHERE username = '%s'", $this->_container['user']));
			if (PEAR::isError($row)) {
				trigger_error(__CLASS__.'::'.__FUNCTION__.' ('.__LINE__.'): '.$row->getDebugInfo(), E_USER_ERROR);
			}
			if (is_array($row)) {
				foreach($row as $key => $value) {
					if (!in_array($key, $this->_container)) {
						$this->_container[$key] = $value;
					}
				}
				$id = (int)$row['id'];
			}
			$_SESSION['_auth'] = serialize($this->_container);
			$db->disconnect();
		} else {
			$sess = unserialize($_SESSION['_auth']);
			$id = (int)$sess['id'];
		}
		return $id;
	}
}
