<?php
/**
 * Globally Smarty-Plugin-Object with some globally methods mostly for
 * checking some actions.
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.1
 * @copyright 2012 Momo Net GmbH
 */
class smarty_globals
{
	/**
	 * Checks, if the given $str are the same uri from request-uri
	 *
	 * @param string $uri
	 * @access public
	 * @since 0.1
	 * @return boolean
	 */
	public function nav_open($str) {
		$uri = $_SERVER['REQUEST_URI'];
		$tmp = explode('/',$uri);
		return ($str == $tmp[1]) ? true : false;
	}
}
