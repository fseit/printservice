<?php
/**
 * DokuWiki Plugin printservice (Helper Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (! defined ( 'DOKU_INC' )) die ();

if (! defined ( 'DOKU_LF' )) define ( 'DOKU_LF', "\n" );
if (! defined ( 'DOKU_TAB' )) define ( 'DOKU_TAB', "\t" );
if (! defined ( 'DOKU_PLUGIN' )) 
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );
require_once ('MDB2.php');

class helper_plugin_printservice_auth extends DokuWiki_Plugin {
	function getInfo() {
		return array (
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2012-08-26',
				'name'   => 'Datenbank-Zugriff',
				'desc'   => 'gemeinsame Funktionen fuer Kontrolle der Zugriffe',
				'url'    => 'http://www.fs-eit.de'
		);
	}
	
	/*function getMethods() {
		$result = array ();
		$result [] = array (
				'name' => 'getBlog', 
				'desc' => 'returns blog entries in reverse chronological order', 
				'params' => array (
						'namespace' => 'string', 
						'number (optional)' => 'integer' 
				), 
				'return' => array (
						'pages' => 'array' 
				) 
		);
		return $result;
	}*/
	
	function isAllowed($area, $username = "") {
		$allowedUsersString = "";
		if ("" == $username) {
			$username = $_SERVER['REMOTE_USER'];
		}
		switch ($area) {
			case "printmapping":
				$allowedUsersString = $this->getConf("user_printmapping");
				break;
			case "lecturematerials":
				$allowedUsersString = $this->getConf("user_lecturematerials");
				break;
			case "mail":
				$allowedUsersString = $this->getConf("user_mail");
				break;
			case "printsummary":
				$allowedUsersString = $this->getConf("user_printsummary");
				break;
			case "printpay":
				$allowedUsersString = $this->getConf("user_printpay");
				break;
			case "printlist":
				$allowedUsersString = $this->getConf("user_printlist");
				break;
			case "printcover":
				$allowedUsersString = $this->getConf("user_printcover");
				break;
			case "ignorelimit":
				 $allowedUsersString = $this->getConf("user_ignorelimit");
				 break;
			default:
				return false;
		}
		
		if ("*" == $allowedUsersString) {
			return true;
		}
		$allowedUsers = explode(',', $allowedUsersString);
		
		foreach ($allowedUsers as $user) {
			if (trim($user) == $username) {
				return true;
			};
		}
		return false;
	}
}
