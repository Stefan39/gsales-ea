<?php
/**
 * Standard Initialisierungs Datei
 * Innerhalb dieser Datei wird das gesamte System als Bootstrap initialisiert
 * und gestartet. Saemtliche Funktions-, Klassen- und andere abhaenige Dateien
 * werden hier inkludiert bzw. entsprechende Behandlungen vorgenommen.
 *
 * @package jcBase
 * @version 0.1
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @copyright 2012 Jacomeit.com
 */

/*
 * Definierung der globalen System-Konstanten
 */
define( 'VERSION', !defined( 'JCB_VERSION' ) ? 0.1 : JCB_VERSION);
define( 'DS', !defined('DS') ? DIRECTORY_SEPARATOR : DS);
define( 'TMP', !defined('TMP') ? dirname(dirname(__FILE__)).'/tmp' : TMP);
define( 'CLASSES', !defined('CLASSES') ? dirname(__FILE__).DS.'includes'.DS.'classes' : CLASSES);
define( 'FUNCTIONS', !defined('FUNCTIONS') ? dirname(__FILE__).DS.'includes'.DS.'functions' : FUNCTIONS);
define( 'SMARTY_DIR', !defined('SMARTY_DIR') ? dirname(__FILE__).DS.'library'.DS.'smarty'.DS : SMARTY_DIR);

// Sets the default for parsing
date_default_timezone_set( 'Europe/Berlin' );

// Set the library, class and functions directories into the include-path
set_include_path(get_include_path().PATH_SEPARATOR.CLASSES.PATH_SEPARATOR.FUNCTIONS);
// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 'on');

//echo ini_get('include_path');

// Delegate own SPL-Autoloader to SPL-Autload
spl_autoload_register('autoloader');

// Starts the config-object
$conf = Configuration::init(dirname(__FILE__).'/includes/config/system.ini');
if ($conf->get('db') == NULL && !$install) {
	HEADER("Location:/install/");
}

// Instantiate the logging
require_once(CLASSES.DS.'Logger.php');
$log_options = array('filename' => TMP.DS.'log.db');
$log = new Logger($log_options);

// Retrieve the Configuration as an array
$config['db'] = array(
		'dsn' => array(
			'username' => 'gsalesea',
			'password' => 'DGY2EncxxtrXV3Ur',
			'database' => 'gsalesea'
		)
);

// Instantiate the neccessary objects
$request = Request::getObject();
$mdb = MySQL::getObject($config['db']);

// Starts authentification
$auth = Authentification::init($mdb);

// Instantiate Smarty
require_once(SMARTY_DIR . 'Smarty.class.php');
$smarty = new smarty;
$smarty->setCompileDir(TMP.DS.'compile');
$smarty->setTemplateDir(dirname(__FILE__).DS.'templates');
$smarty->setCaching(false);
// Assign some global objects
$smarty->assign('auth', $auth);
$smarty->assign('log_entries', $log->sumEntries());

/**
 * Includes all existing smarty-functions
 */
$smarty_funcs = scandir(FUNCTIONS.'/smarty/');
if (is_array($smarty_funcs)){
	foreach($smarty_funcs as $func){
		if (preg_match('/\.php$/i',$func)){
			include_once(FUNCTIONS.'/smarty/'.$func);
			if ($func == "globals.php") {
				$smarty->assign('setup', new smarty_globals());
			}
		}
	}
}
/**
 * Load all the other functions into the directory functions
 */
$funcs = scandir(FUNCTIONS);
if (is_array($funcs)){
	foreach($funcs as $func) {
		if (preg_match('/(.)\.php/',$func)) {
			include_once(FUNCTIONS.DS.$func);
		}
	}
}

/**
 * SPL-Autloader
 */
function autoloader($class) {
	$classfile = (strpos($class, '_') ? str_replace('_', DS, $class) : $class).'.php';
	$dirs = explode(PATH_SEPARATOR, get_include_path());
	foreach($dirs as $dir) {
		if (file_exists($dir.DS.$classfile))
			include_once($dir.DS.$classfile);
	}
}