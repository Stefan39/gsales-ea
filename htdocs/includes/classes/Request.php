<?php
/**
 * Request-Class
 * Handles all global request-parameters and gives methodes to check this
 * filtered.
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 1.1
 */
class Request
{
	/**
	 * Instantiate Object
	 * @staticvar $_object
	 * @access private
	 */
	static private $_object = NULL;
	/**
	 * Parameter-Array
	 * @var array
	 * @access private
	 */
	private $_params = array();
	/**
	 * Private construction
	 * This object can be instantiate within the methode getObject() only and
	 * cannot instantiate with normally class-construct.
	 *
	 * @access private
	 * @return void
	 */
	private function __construct() {
		// For security reasons and if exists, delete the PHP_AUTH_PW variable:
		if (isset($_SERVER['PHP_AUTH_PW']))
			unset($_SERVER['PHP_AUTH_PW']);

		$this->_params = array_merge($_GET, $_POST, $_SERVER);
		if (session_id())
			$this->_params = array_merge($this->_params, $_SESSION);
	}
	/**
	 * Klonen verbieten
	 * @access private
	 * @return void
	 */
	private function __clone() {}
	/**
	 * Instantiate the object
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 * @return object Request $_object
	 */
	static public function getObject() {
		if (is_null(self::$_object)) {
			self::$_object = new self;
		}
		return self::$_object;
	}
	/**
	 * Magically Methode to returned the Parameter $param from the _params
	 * Class - Array if exists. If not exists, this magically methode will
	 * return a false.
	 *
	 * @param mixed $key
	 * @access public
	 * @since 1.0
	 * @return mixed
	 */
	public function __get($key) {
		return isset($this->_params[$key]) ? $this->_filtered($this->_params[$key]) : FALSE;
	}
	/**
	 * Magically Methode to check, if an param exists. So this function can
	 * using into the isset($request->param) function for checking if the
	 * parameter are set or not.
	 *
	 * @param mixed $key
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function __isset($key) {
		return isset($this->_params[$key]);
	}
	/**
	 * Methode to check, if the $key is into the POST-Array.
	 *
	 * @param mixed $key
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function isPost($key) {
		return (isset($this->_params[$key]) && isset($_POST[$key]));
	}
	/**
	 * Methode to check if the $key is into the SESSION-Array
	 *
	 * @param mixed $key
	 * @access public
	 * @since 1.1
	 * @return bool
	 */
	public function isSession($key) {
		return (isset($this->_params[$key]) && isset($_SESSION[$key]));
	}
	/**
	 * Returned all parameters into the class param-array unfiltered!
	 *
	 * @access public
	 * @since 1.1
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}
	/**
	 * Returned all POST-Parameter as array
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function getPostParams() {
		return $_POST;
	}
	/**
	 * Returned unfiltered $key from the Params
	 *
	 * @param mixed $key
	 * @access public
	 * @since 1.1
	 * @return mixed
	 */
	public function unfiltered($key) {
		return isset($this->_params[$key]) ? $this->_params[$key] : FALSE;
	}
	/**
	 * Processing the filter of the param with $value and returned the filtered
	 * value of this.
	 *
	 * @param mixed $value
	 * @access private
	 * @since 1.1
	 * @return mixed
	 */
	private function _filtered($value) {
		switch(gettype($value)) {
			case 'string':
				$return = filter_var($value, FILTER_SANITIZE_STRING);
				break;
			case 'integer':
				$return = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
				break;
			case 'double':
				$return = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
				break;
			default:
				$return = $value;
		}
		return isset($return) ? $return : FALSE;
	}
}
