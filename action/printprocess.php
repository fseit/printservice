<?php
/**
 * DokuWiki Plugin printservice (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Florian Rinke <rinke.florian@web.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';
require_once('MDB2.php');

class action_plugin_printservice_printprocess extends DokuWiki_Action_Plugin {
	
	public function register($controller) {
		$controller->register_hook ( 'ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_printprocess' );
	}
	
	public function handle_printprocess(Doku_Event &$event, $param) {
		if (! isset ( $_REQUEST ['action'] )) {
			return true;
		}
		
		if (! in_array ( $_REQUEST ['action'], array ('order_create', 'order_send', 'order_cancel' ) )) {
			//msg("Unknown Action: ".$_REQUEST['action']);
			return true;
		} else {
			$act = $_REQUEST ['action'];
			$this->dbConnect ();
		}
		
		$state = $this->fetchOrderState ( $_SERVER ['REMOTE_USER'] );
		if ($this->getConf ( 'active' ) == 0 || ! ($state == 'notfound' || $state == 'unpaid')) {
			msg ( $this->getLang ( 'msg_reallyclosed' ) );
			return true;
		}
		
		if ($act == 'order_create') {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return false;
			}
			$this->createOrder ();
			msg ( $this->getLang ( 'msg_created' ) );
		}
		if ($act == 'order_send') {
			if (is_array ( $_REQUEST ['orderId'] )) {
				if (! checkSecurityToken ()) {
					msg ( "nanana!" );
					return false;
				}
				$add = array ();
				foreach ( $_REQUEST ['orderId'] as $value ) {
					if (is_numeric ( $value ))
						$add [] = ( int ) $value;
				}
				if ($this->sendOrder ( $add )) {
					msg ( $this->getLang ( 'msg_added' ) );
				}
			}
		}
		if ($act == 'order_cancel') {
			if (is_array ( $_REQUEST ['stornoId'] )) {
				if (! checkSecurityToken ()) {
					msg ( "nanana!" );
					return false;
				}
				$delete = array ();
				foreach ( $_REQUEST ['stornoId'] as $value ) {
					if (is_numeric ( $value ))
						$delete [] = ( int ) $value;
				}
				if ($this->deleteOrders ( $delete )) {
					msg ( $this->getLang ( 'msg_deleted' ) );
				}
			}
		
		}
	}
	
	public function handle_printprocess_tpl(Doku_Event &$event, $param) {
		echo "tpl<br />\n";
		$event->stopPropagation ();
		$event->preventDefault ();
		return false;
	}
	
	private function dbConnect() {
		$dsn = 'mysqli://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage () );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		return true;
	}
	
	private function createOrder() {
		$sql = 'INSERT INTO ' . $this->getConf ( 'db_prefix' ) . 'orders (semester, user) VALUES (?,(SELECT user_id FROM phpbb_users WHERE username=?))';
		$sqldata = array ($this->getConf ( 'semester' ), $_SERVER ['REMOTE_USER'] );
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare1: " . $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec1: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	private function sendOrder($ids) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'INSERT INTO `skript_orderitems`(`order`, `file`, `format`, `duplex`, `price`) ';
		$sql .= 'VALUES (:order, :file, :format, :pagemode, :price)';
		$sqltype = array ('integer', 'text', 'text', 'text', 'decimal' );
		
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare3: " . $query->getMessage () );
			return false;
		}
		$alldata = array ();
		foreach ( $ids as $value ) {
			$price = $this->fetchPrice ( $value, $this->getConf ( 'pagecost' ), ($_REQUEST ['format'] == 'a4' ? 1 : 0.5) );
			$alldata [] = array ('file' => $value, 'price' => $price, 'order' => $this->fetchOrderId (), 'format' => ($_REQUEST ['format'] == 'a4' ? 'a4' : 'a5'), 'pagemode' => ($_REQUEST ['pagemode'] == 'duplex' ? 'duplex' : 'simplex') );
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $alldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec2: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	private function deleteOrders($ids) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'SET i.deleted = 1 ';
		$sql .= 'WHERE i.id= ? AND i.order IN ';
		$sql .= '(SELECT DISTINCT o.id FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN phpbb_users u ON o.user=u.user_id ';
		$sql .= 'WHERE u.username = ' . $this->mdb2->quote ( $this->mdb2->escape ( $_SERVER ['REMOTE_USER'] ) ) . ')';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare3: " . $query->getMessage () );
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec3: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	private function fetchOrderId() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT id FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "JOIN phpbb_users u ON u.user_id=o.user ";
		$sql .= "WHERE u.username=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($_SERVER ['REMOTE_USER'] ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query4: " . $res->getMessage () );
		}
		return $row;
	}
	
	private function fetchPrice($id, $pagecost, $factor) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT pages FROM " . $this->getConf ( 'db_prefix' ) . "documents o ";
		$sql .= "WHERE id=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($id ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query4: " . $res->getMessage () );
		}
		$price = round ( (( int ) $row * $pagecost + 5) / 2, - 1 ) / 50 * $factor;
		return $price;
	}
	
	private function fetchOrderState($user) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT paymentState, deliveryState FROM " . $this->getConf ( 'db_prefix' ) . "orders o JOIN phpbb_users u ON u.user_id=o.user WHERE u.username=?";
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $user );
		if (PEAR::isError ( $res )) {
			echo "Query3: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res->numRows () == 0) {
			return 'notfound';
		}
		$row = $res->fetchRow ();
		$res->free ();
		if ($row ['paymentstate'] == 'unpaid') {
			return "unpaid";
		} else
			return $row ['deliverystate'];
	}
}

// vim:ts=4:sw=4:et:
