<?php
/**
 * Network Functions file with all neccessary functions to handle all.
 *
 * @package netcrawler
 * @Author Stefan Jacomeit <stefan@jacomeit.com>
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
 * Checks, if the networkname exists into the storage
 *
 * @param string $networkname
 * @since 0.1
 * @return boolean
 */
function network_exists($networkname) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT id FROM netze WHERE networkname = '%s'", $networkname), array('integer'), 0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$id = isset($res['id']) ? true : false;
	$db->disconnect();
	return $id;
}
/**
 * Return an array with all networks into the storage + the amount of domains
 * into a network
 *
 * @since 0.1
 * @return mixed		An Array or false, if no results returned
 */
function network_getAll() {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$data = false;
	$res = $db->queryAll("SELECT n.id, n.subnetz, DATE_FORMAT(n.created,'%d.%c.%Y %H:%i:%s') AS created, n.networkname FROM netze AS n");
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	if (is_array($res)) {
		foreach($res as $erg) {
			$data[] = array(
				'id' => $erg['id'],
				'subnetz' => $erg['subnetz'],
				'created' => $erg['created'],
				'networkname' => $erg['networkname'],
				'domains' => domain_into_network((int)$erg['id'])
			);
		}
	}
	$db->disconnect();
	return $data;
}
/**
 * Add a new Network to the storage
 *
 * @param array $data 	with Key: networkname,
 * @since 0.1
 * @return mixed		An integer if ok, otherwise false
 */
function network_add(array $data) {
	if (network_exists($data['networkname'])) return false;
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$pre = $db->prepare("INSERT INTO netze (subnetz,networkname) VALUES (:subnetz, :networkname)", array('integer','text'), MDB2_PREPARE_MANIP);
	if (PEAR::isError($pre)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$pre->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	$exc = $pre->execute(array(
		'subnetz' => !empty($data['subnetz']) ? $data['subnetz'] : null,
		'networkname' => $data['networkname']
	));
	if (PEAR::isError($exc)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$exc->getDebugInfo(), E_USER_ERROR);
		return false;
	}
	$id = (int)$db->lastInsertId('netze', 'id');
	$db->disconnect();
	return isset($id) ? (int)$id : false;
}
/**
 * Checks, if $id is a subnetwork from another network and if so, the network
 * ID from this will returned.
 *
 * @param integer $id
 * @since 0.1
 * @return mixed		An ID as Integer or null, if not
 */
function network_isSubnet($id) {
	$mdb = MySQL::getObject();
	$db = $mdb->getDb();
	$res = $db->queryOne(sprintf("SELECT subnetz FROM netze WHERE id = %d", (int)$id), array('integer'),0);
	if (PEAR::isError($res)) {
		trigger_error(__FUNCTION__.' ('.__LINE__.'): '.$res->getDebugInfo(),E_USER_ERROR);
		return false;
	}
	$id = isset($res) ? (int)$res : null;
	$db->disconnect();
	return $id;
}