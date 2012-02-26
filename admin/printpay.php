<?php
/**
 * DokuWiki Plugin printservice (Admin Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'admin.php';

class admin_plugin_printservice_printpay extends DokuWiki_Admin_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Kasse',
				'desc'   => 'Verwaltet Bezahlungen',
				'url'    => 'http://www.fs-eit.de',
		);
	}
	
	public function getMenuSort() {
		return FIXME;
	}
	public function forAdminOnly() {
		return true;
	}
	public function getMenuText() {
		return $this->getLang ( 'menu_printpay' );
	}
	public function handle() {
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		
		//delete entries
		if (is_array ( $_REQUEST ['stornoId'] )) {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return;
			}
			$delete = array ();
			foreach ( $_REQUEST ['stornoId'] as $value ) {
				if (is_numeric ( $value ))
					$delete [] = ( int ) $value;
			}
			$dbhelper->deleteOrders ( $delete );
			msg ( $this->getLang ( 'msg_deleted' ) );
		}
		
		//store payment
		if (isset ( $_REQUEST ['payment'] )) {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return;
			}
			//echo "pm_" . $_REQUEST ['payment'] . "_pm";
			$dbhelper->storePayment ( $_REQUEST ['payment'] + 0 );
			msg ( $this->getLang ( 'msg_pais' ) );
		}
	
	}
	
	public function html() {
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		
		ptln ( '<h1>' . $this->getLang ( 'menu_printpay' ) . '</h1>' );
		//Search field
		$form = new Doku_Form ( array ('id' => 'namesearch', 'method' => 'POST' ) );
		$form->addHidden ( 'page', 'printservice_printpay' );
		$form->addElement ( form_makeTextField ( 'namesearch', hsc ( $_REQUEST ['namesearch'] ), 'Name:' ) );
		$form->addElement ( form_makeButton ( 'submit', 'admin', $value = 'Suchen' ) );
		$form->printForm ();
		
		//Select field
		if (isset ( $_REQUEST ['namesearch'] )) {
			$names = $dbhelper->searchUser ( $_REQUEST ['namesearch'] );
			$form = new Doku_Form ( array ('id' => 'selectuser', 'method' => 'POST' ) );
			$form->addHidden ( 'page', 'printservice_printpay' );
			$form->startFieldSet ( $this->getLang ( 'field_foundusers' ) );
			$form->addElement ( "<table><tr><th></th><th>" . $this->getLang ( 'tbl_realname' ) . "</th><th>" . $this->getLang ( 'tbl_username' ) . "</th></tr>" );
			while ( $row = $names->fetchRow () ) {
				$form->addElement ( "<tr><td><input type=\"radio\" name=\"userselect\" value=\"" . $row ['username'] . "\" /></td><td>" . $row ['realname'] . "</td><td>" . $row ['username'] . "</td></tr>" );
			}
			$form->addElement ( "</table>" );
			$form->addElement ( form_makeButton ( 'submit', 'admin', $value = $this->getLang ( 'btn_choose' ) ) );
			$form->endFieldSet ();
			$form->printForm ();
			$names->free ();
		}
		//show order
		if (isset ( $_REQUEST ['userselect'] )) {
			$state = $dbhelper->fetchOrderState ( $_REQUEST ['userselect'] );
			if (! state) {
				ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'note_hasnoorder' ) . "</div></p>" );
			} else if ($state != 'unpaid') {
				ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'note_paid' ) . "</div></p>" );
			} else {
				$orderid = - 1;
				$res = $dbhelper->fetchOrder ( $_REQUEST ['userselect'] );
				$form = new Doku_Form ( array ('id' => 'myorders' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				$form->addHidden ( 'userselect', hsc ( $_REQUEST ['userselect'] ) );
				$form->startFieldSet ( "Bestellung von " . $_REQUEST ['userselect'] . " im " . $dbhelper->fetchSemester ($this->getConf ( 'semester' )) );
				$form->addElement ( "<table>\n<tr>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_doc' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_format' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pagemode' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pages' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_price' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_storno' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_comment' ) . "</th>" );
				$form->addElement ( "</tr>" );
				while ( $row = $res->fetchRow () ) {
					$form->addElement ( "<tr>" );
					$form->addElement ( "<td><a href=\"{$row['filename']}\">{$row['title']}: {$row['filename']}</a></td>" ); //Skript
					$form->addElement ( "<td>" . ($row ['format'] == "a4" ? $this->getLang ( 'tbl_a4' ) : $this->getLang ( 'tbl_a5' )) . "</td>" ); //Format
					$form->addElement ( "<td>" . ($row ['duplex'] == "simplex" ? $this->getLang ( 'tbl_simplex' ) : $this->getLang ( 'tbl_duplex' )) . "</td>" ); //Doppelseitig
					$form->addElement ( "<td>{$row['pages']}</td>" ); //Preis
					$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $row ['price'] ) . "</td>" ); //Preis
					$form->addElement ( "<td><input type=\"checkbox\" name=\"stornoId[]\" value=\"{$row['id']}\" /></td>" ); //Stornieren
					$form->addElement ( "<td>{$row['comment']}</td>" ); //Hinweis
					$form->addElement ( "</tr>\n" );
					$orderid = $row ['orderid'];
				}
				$form->addElement ( "</table>" );
				$form->addElement ( "<input type=\"submit\" value=\"" . $this->getLang ( 'btn_cancel' ) . "\" />" );
				$form->endFieldSet ();
				$form->printForm ();
				$res->free ();
				
				$form = new Doku_Form ( array ('id' => 'payment' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				if ($orderid != - 1) {
					$form->addHidden ( 'payment', $orderid );
				}
				$form->startFieldSet ( $this->getLang ( 'field_payment' ) );
				$form->addElement ( "<table>" );
				$form->addElement ( "<tr>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_total' ) . "</th><th></th><th></th><th>" . sprintf ( "%.2f &euro;", $this->fetchPricesum ( $_REQUEST ['userselect'] ) ) . "</th><th></th><th></th></tr>\n" );
				$form->addElement ( "</table>" );
				$form->addElement ( "<input type=\"submit\" value=\"" . $this->getLang ( 'btn_markpaid' ) . "\" />" );
				$form->endFieldSet ();
				$form->printForm ();
			
			}
		}
	}
	
	/*private function dbConnect() {
		$dsn = 'mysql://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage () );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		$this->mdb2->setCharset('utf8');
		return true;
	}
	
	private function fetchSemester() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT name FROM " . $this->getConf ( 'db_prefix' ) . "semesters WHERE code=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($this->getConf ( 'semester' ) ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query1: " . $res->getMessage () );
		}
		return $row;
	}
	
	private function fetchOrder($user) {
		$sql = 'SELECT d.title, i.format, i.duplex, d.pages, i.price, d.comment, i.id, o.id as orderid, d.filename, o.paymentState ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'WHERE u.username = ? AND i.deleted=0 ';
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $user );
		if (PEAR::isError ( $res )) {
			echo "Query2: " . htmlentities ( $this->resOwnOrder->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	
	private function searchUser($user) {
		$sql = 'SELECT u.username, p.pf_realname as realname FROM phpbb_users u JOIN phpbb_profile_fields_data p ON p.user_id=u.user_id WHERE u.username REGEXP ? OR p.pf_realname REGEXP ?';
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo $query->getMessage ();
			return false;
		}
		$res = $query->execute ( array ($user, $user ) );
		if (PEAR::isError ( $res )) {
			echo "Query3: " . htmlentities ( $this->resOwnOrder->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	
	private function fetchPricesum($user) {
		$sql = "SELECT sum(i.price) ";
		$sql .= 'FROM skript_orderitems i ';
		$sql .= 'JOIN skript_orders o ON o.id = i.order ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'WHERE o.semester = ? AND u.username = ?  AND i.deleted=0 ';
		$sqltype = array ('text', 'text' );
		$sqldata = array ($this->getConf ( 'semester' ), $user );
		$row = $this->mdb2->extended->getOne ( $sql, null, $sqldata, $sqltype );
		if (PEAR::isError ( $res )) {
			die ( "Query4: " . $res->getMessage () );
		}
		return $row;
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
		} elseif ($res == DB_OK or empty ( $res )) {
			echo "notfound";
			return 'notfound';
		}
		$row = $res->fetchRow ();
		$res->free ();
		if ($row ['paymentstate'] == 'unpaid') {
			return "unpaid";
		} else
			return $row ['deliverystate'];
	}
	
	private function deleteOrders($ids) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'SET i.deleted = 1 ';
		$sql .= 'WHERE i.id= ?';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare5: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec5: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	private function storePayment($order) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "UPDATE `" . $this->getConf ( 'db_prefix' ) . "orders` ";
		$sql .= "SET paymentState='paid' WHERE id=?";
		$sqltype = array ('integer' );
		$res = $query = $this->mdb2->prepare ( $sql, $sqltype );
		$query->execute ( $order );
        if (PEAR::isError($res)) {
            die("Query6: ".$res->getMessage());
        }
        return true;
    }*/
}

// vim:ts=4:sw=4:et:
