<?php
/**
 * Globally functions to use this into the project
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.1
 * @copyright 2012 Momo-Net GmbH
 */

/**
 * Make a redirection to site $site
 *
 * @param string $site
 * @since 0.1
 * @return void
 */
function redirect($site='/') {
	header("Status: 301 Moved Permanently");
	header("Location: ".$site);
	exit;
}