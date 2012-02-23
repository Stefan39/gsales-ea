<?php
/**
 * Ajax-Handler
 *
 * @package netcrawler
 * @author Stefan Jacomeit <stefan@jacomeit.com>
 * @version 1.0
 */
include_once('init.php');
$action = $request->action;
switch($action) {
	case 'check':
		$for = $request->for;
		switch($for) {
			case 'domainname':
				$exist = domain_exists($request->domain);
				$return = $exist ? array('error' => 'Domain existiert bereits') : array('success'=>true);
				break;
		}
		break;

	case 'save':
		$do = $request->do;
		$name = $request->name;
		switch($do){
			case 'server':
				$return = ($id = server_save(array('name'=>$name))) ? array('success'=>true, 'id' => $id) : array('error'=>'An error occured');
				break;
			case 'network':
				$return = ($id = network_add(array('networkname'=>$name))) ? array('success'=>true, 'id'=>$id, 'subnetz' => network_isSubnet($id)) : array('error'=>'An error occured');
				break;
		}
		break;

	default:
		$return = array('error'=>'Diese Aktion exisitiert nicht und Aktion wurde nicht angegeben.');
}
header("Content-Type: application/json");
echo json_encode($return);
exit;