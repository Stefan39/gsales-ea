<?php
/**
 * Functions-File with functions to handle the server-properties
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 0.1
 * @copyright 2012 Momo-Net GmbH
 */

/**
 * Changelog
 *
 * 0.1
 * - initial creation
 */

/**
 * Checks, if a server-name exists
 *
 * @param string $name
 * @since 0.1
 * @return boolean
 */
function server_exists($name) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT id FROM server WHERE servername = '%s'", $name), array('integer'), 0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$id = empty($res->id) ? false : true;
	$db->disconnect();
	return $id;
}

/**
 * Retrieve all saved Server and returns an array
 *
 * @since 0.1
 * @return array
 */
function server_getAll() {
	$data = null;
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryAll("SELECT id, servername FROM server");
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	if (is_array($res)) {
		foreach($res as $erg) {
			$data[] = array(
				'id' => $erg['id'],
				'servername' => $erg['servername'],
				'domains' => domain_into_server((int)$erg['id'])
			);
		}
	}
	$db->disconnect();
	return $data;
}

/**
 * Save the $data Array with Key 'name' into the Storage-Table 'server'
 *
 * @param array $data
 * @since 0.1
 * @return mixed		Integer ID or false, if an error occured
 */
function server_save(array $data) {
	if (false !== server_exists($data['name'])){return false;}
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$pre = $db->prepare("INSERT INTO server (servername) VALUES (?)", array('text'), MDB2_PREPARE_MANIP);
	if (PEAR::isError($pre)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$pre->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$exc = $pre->execute($data['name']);
	if (PEAR::isError($exc)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$exc->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$id = (int)$db->lastInsertId('server', 'id');
	$db->disconnect();
	return $id;
}