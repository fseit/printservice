<?php
/**
 * DokuWiki Plugin printservice (Action Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (! defined ( 'DOKU_INC' )) die ();

if (! defined ( 'DOKU_LF' )) define ( 'DOKU_LF', "\n" );
if (! defined ( 'DOKU_TAB' )) define ( 'DOKU_TAB', "\t" );
if (! defined ( 'DOKU_PLUGIN' )) define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'action.php';

class action_plugin_printservice_printprocess extends DokuWiki_Action_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Bearbeitung',
				'desc'   => 'bearbeitet Anfragen der Syntax-Plugins',
				'url'    => 'http://www.fs-eit.de'
		);
	}
	
	public function register($controller) {
		$controller->register_hook ( 'ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_printprocess' );
	}
	
	public function handle_printprocess(Doku_Event &$event, $param) {
		if (! isset ( $_REQUEST ['action'] )) {
			return false;
		}
		
		if (! in_array ( $_REQUEST ['action'], array ('order_create', 'order_send', 'order_cancel' ) )) {
			// msg("Unknown Action: ".$_REQUEST['action']);
			return false;
		} else {
			$act = $_REQUEST ['action'];
			$dbhelper = & plugin_load ( 'helper', 'printservice_database' );
			$dbhelper->dbConnect ();
		}
		
		$state = $dbhelper->fetchOrderState ( $_SERVER ['REMOTE_USER'], $this->getConf ( 'semester' ) );
		
		if (! checkSecurityToken ()) {
			msg ( "nanana!" );
			return false;
		}
		
		if ($act == 'order_create') {
			if ($this->getConf ( 'active' ) == 0 || $state == 'unpaid') {
				msg ( $this->getLang ( 'msg_reallyclosed' ) );
				return true;
			}
			$dbhelper->createOrder ( $this->getConf ( 'semester' ), $_SERVER ['REMOTE_USER'] );
			msg ( $this->getLang ( 'msg_created' ) );
		}
		
		if ($act == 'order_send') {
			if (($this->getConf ( 'active' ) == 0 || $state != 'unpaid')) {
				msg ( $this->getLang ( 'msg_reallyclosed' ) );
				return true;
			}
			if (is_array ( $_REQUEST ['orderId'] )) {
				$add = array ();
				foreach ( $_REQUEST ['orderId'] as $value ) {
					if (is_numeric ( $value ))
						$add [] = ( int ) $value;
				}
				$options = array ('order' => ( int ) $dbhelper->fetchOrderId ( $_SERVER ['REMOTE_USER'], $this->getConf ( 'semester' ), 'unpaid' ), 'format' => ($_REQUEST ['format'] == 'a5' ? 'a5' : 'a4'), 'pagemode' => ($_REQUEST ['pagemode'] == 'simplex' ? 'simplex' : 'duplex') );
				if ($dbhelper->sendOrder ( $add, $options )) {
					msg ( $this->getLang ( 'msg_added' ) );
				}
			}
		}
		if ($act == 'order_cancel') {
			if (($this->getConf ( 'active' ) == 0 || $state != 'unpaid')) {
				msg ( $this->getLang ( 'msg_reallyclosed' ) );
				return true;
			}
		}
		echo "cancel";
		if (is_array ( $_REQUEST ['stornoId'] )) {
			$delete = array ();
			foreach ( $_REQUEST ['stornoId'] as $value ) {
				if (is_numeric ( $value ))
					$delete [] = array ('id' => ( int ) $value, 'user' => $_SERVER ['REMOTE_USER'] );
			}
			if ($dbhelper->deleteOrders ( $delete, $_SERVER ['REMOTE_USER'] )) {
				msg ( $this->getLang ( 'msg_deleted' ) );
			}
		}
	
	}
	
	public function handle_printprocess_tpl(Doku_Event &$event, $param) {
		echo "tpl<br />\n";
		$event->stopPropagation ();
		$event->preventDefault ();
		return false;
	}
}
