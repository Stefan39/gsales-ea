<?php
/**
 * Configuration Class
 * This class works with PEAR::Config_Lite for reading / writing the
 * configuration file under /includes/config.
 *
 * @package gsales-ea
 * @version 0.1
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @copyright 2012 Jacomeit.com
 */
class Configuration
{
	/**
	 * Config-Object
	 * @var object PEAR::Config_Lite
	 * @access private
	 */
	private $_config = null;
	/**
	 * Class Object
	 * @staticvar object
	 * @access private
	 */
	static private $_object = null;
	/**
	 * Forbid cloning
	 * @access private
	 */
	private function __clone() {}
	/**
	 * Instantiate the object
	 * @param string $conffile
	 * @access public
	 * @static
	 * @since 0.1
	 * @return object
	 */
	static public function init($conffile) {
		if (is_null(self::$_object)) {
			self::$_object = new self($conffile);
		}
		return self::$_object;
	}

	/**
	 * Private constructor of the class
	 * @param string $conffile
	 * @access private
	 * @since 0.1
	 * @return void
	 */
	private function __construct($conffile) {
		$this->_config = new Config_Lite($conffile);
	}
	/**
	 * Get an entry from the config
	 * @param $key
	 * @access public
	 * @since 0.1
	 * @return mixed
	 */
	public function get($key) {
		try {
			return $this->_config->get($key);
		} catch (Config_Lite_Exception $e) {
			return NULL;
		}
	}
}
