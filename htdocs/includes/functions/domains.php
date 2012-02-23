<?php
/**
 * Functionsfile for handling Domains into the storage
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
 * - Initial creation
 */

/**
 * Checks, if the Domain $domain exists and returned a boolean
 *
 * @param string $domain
 * @since 0.1
 * @return bool
 */
function domain_exists($domain) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT id FROM domains WHERE domainname = '%s'", $domain), array('integer'), 0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	$id = isset($res['id']) ? true : false;
	$db->disconnect();
	return $id;
}
/**
 * Return the amount of founded Domains into the network with ID $netid.
 *
 * @param integer $netid
 * @since 0.1
 * @return int
 */
function domain_into_network($netid) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT COUNT(id) AS amount FROM domains WHERE netz = %d", (int)$netid),array('integer'),0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
	}
	$sum = isset($res['amount']) ? (int)$res['amount'] : 0;
	$db->disconnect();
	return $sum;
}
/**
 * Return the amount of founded Domains into the server with ID $serverid
 *
 * @param integer $serverid
 * @since 0.1
 * @return int
 */
function domain_into_server($serverid) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT COUNT(id) AS amount FROM domains WHERE server = %d", (int)$serverid), array('integer'),0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
	}
	$sum = isset($res['amount']) ? (int)$res['amount'] : 0;
	$db->disconnect();
	return $sum;
}
/**
 * Get all domains from Storage with the network and other infos
 *
 * @since 0.1
 * @return mixed	An array on success or false on error
 */
function domain_getAll() {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$data = null;
	$res = $db->queryAll("SELECT d.id, d.server, s.servername, d.netz, n.networkname, DATE_FORMAT(d.created,'%d.%c.%Y %H:%i:%s') AS created, d.domainname
		FROM domains AS d
		JOIN server AS s ON s.id = d.server
		JOIN netze AS n ON n.id = d.netz");
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
	}
	if (is_array($res)) {
		foreach($res as $row) {
			$data[] = array(
				'id' => $row['id'],
				'created' => $row['created'],
				'domainname' => $row['domainname'],
				'serverid' => $row['server'],
				'servername' => $row['servername'],
				'netid' => $row['netz'],
				'network' => $row['networkname'],
				'spiderd' => false
			);
		}
	}
	$db->disconnect();
	return $data;
}
/**
 * Save the new Domain into the storage
 *
 * @param array $data
 * @since 0.1
 * @return mixed 	The ID as an Integer or false, if error
 */
function domain_save(array $data) {
	if (domain_exists($data['domainname'])) return false;
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$pre = $db->prepare("INSERT INTO domains (server, netz, domainname) VALUES (:server, :netz, :domainname)",array('integer','integer','text'),MDB2_PREPARE_MANIP);
	if (PEAR::isError($pre)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$pre->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$exc = $pre->execute(array(
		'server' => (int)$data['server'],
		'netz' => (int)$data['netz'],
		'domainname' => $data['domainname']
	));
	if (PEAR::isError($exc)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$exc->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	$id = $db->lastInsertId('domains','id');
	$db->disconnect();
	return $id;
}