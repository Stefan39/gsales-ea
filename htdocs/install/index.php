<?php
/**
 * Installs the gsales-ea system
 *
 * @package gsales-ea
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.1
 * @copyright Jacomeit.com
 */
$install=true;
include_once('../init.php');
$action = $request->action;
switch($action) {
	case 'step2':
		$post = $request->getPostParams();
		if (!$post['hostspec'] || !$post['database']) {
			$smarty->assign("error", "Die Angabe des Hostnames und Datenbankname ist erforderlich");
		} else {
			$dsn = array(
				'hostspec' => $request->hostspec,
				'database' => $request->database,
				'username' => $request->username,
				'password' => $request->password
			);
			$db = MySQL::getObject(array('dsn'=>$dsn));
			if (true===$db->testDb($dsn)) {
				if (install_tables($db)) {
					header("Location:/install/?step=3");
					exit;
				}
			}
		}
		break;

	case 'step1':
		$title = 'Installation 1. Schritt gSales-EA';
		$tpl = 'install/step1.html';
		$data = array();
		break;

	default:
		$title = 'Installation von gSales-EA';
		$tpl = 'install/index.html';
		$data = array();
}
$smarty->assign('title',$title);
$smarty->assign('data', $data);
$smarty->display($tpl);

/**
 * Function to installing the Tables
 * @param object $mysql 	MySQL-Object
 * @return bool
 */
function install_tables(MySQL $mysql) {
	$sql = file_get_contents(dirname(__FILE__).'/creates.sql');
	$db = $mysql->getDb();
	$queries = explode(';',$sql);
	foreach($queries as $query) {
		$qry = $db->query($query);
		if (PEAR::isError($qry)) {
			trigger_error('DB-Error: '.$qry->getDebugMessage(),E_USER_ERROR);
			return false;
		}
	}
	return true;
}