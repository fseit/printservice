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
		return false;
	}
	public function getMenuText() {
		return $this->getLang ( 'menu_printpay' );
	}
	public function handle() {
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printpay")) {
			return;
		}
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
					$delete [] = array( 'id' => ( int ) $value);
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
			msg ( $this->getLang ( 'msg_paid' ) );
		}
	
	}
	
	public function html() {
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printpay")) {
			ptln(hsc($this->getLang('msg_notauthorized')));
			return;
		}
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		
		ptln ( '<h1>' . $this->getLang ( 'menu_printpay' ) . '</h1>' );
		//Search field
		$form = new Doku_Form ( array ('id' => 'namesearch', 'method' => 'POST' ) );
		$form->addHidden ( 'page', 'printservice_printpay' );
		$form->addElement ( form_makeTextField ( 'namesearch', hsc ( $_REQUEST ['namesearch'] ), 'Name:' ) );
		$form->addElement ( form_makeButton ( 'submit', 'admin', $value = 'Suchen' ) );
		$form->printForm ();
			
			// Select field
		if (isset ( $_REQUEST ['namesearch'] )) {
			if (! $res = $dbhelper->searchOrdersByUser ( $_REQUEST ['namesearch'], $this->getConf ( 'semester' ) )) {
				ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'err_nolist' ) . "</div></p>" );
			} else {
				$form = new Doku_Form ( array ('id' => 'selectuser', 'method' => 'POST' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				$form->startFieldSet ( $this->getLang ( 'field_foundusers' ) );
				$form->addElement ( "<table>\n<tr>" );
				$form->addElement ( "<th colspan=\"2\">" . $this->getLang ( 'tbl_orderid' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_customer' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_username' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pages' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_price' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_status' ) . "</th>" );
				$form->addElement ( "</tr>" );
				while ( $row = $res->fetchRow () ) {
					$form->addElement ( "<tr>" );
					$form->addElement ( "<td><input type=\"radio\" name=\"orderid\" value=\"" . $row ['id'] . "\" /></td>" );
					$form->addElement ( "<td>{$row['id']}</td>" );
					$form->addElement ( "<td>{$row['realname']}</td>" );
					$form->addElement ( "<td>{$row['username']}</td>" );
					$form->addElement ( "<td>{$row['pages']}</td>" );
					$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $row ['price'] ) . "</td>" );
					$form->addElement ( "<td>" . $this->getLang ( $row ['orderstate'] ) . "</td>" );
					$form->addElement ( "</tr>\n" );
				}
				$form->addElement ( "</table>" );
				$form->addElement ( form_makeButton ( 'submit', 'admin', $value = $this->getLang ( 'btn_choose' ) ) );
				$form->endFieldSet ();
				$form->printForm ();
				$res->free (); 
			}
		}
		//show order
		if (isset ( $_REQUEST ['orderid'] )) {
			$state = $dbhelper->fetchOrderStateById ( $_REQUEST ['orderid'] );
			if (! state) {
				ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'note_hasnoorder' ) . "</div></p>" );
			} else if ($state != 'unpaid') {
				ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'note_paid' ) . "</div></p>" );
			} else {
				$orderid = - 1;
				$items = $dbhelper->fetchOrderItemsByIds ( $_REQUEST ['orderid'] );
				$form = new Doku_Form ( array ('id' => 'myorders' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				$form->addHidden ( 'orderid', hsc ( $_REQUEST ['orderid'] ) );
				$form->startFieldSet ( "Bestellung Nr. " . $_REQUEST ['orderid'] . " im " . $dbhelper->fetchSemester ($this->getConf ( 'semester' )) );
				$form->addElement ( "<table>\n<tr>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_doc' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_format' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pagemode' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pages' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_price' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_storno' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_comment' ) . "</th>" );
				$form->addElement ( "</tr>" );
				foreach($items as $row) {
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
				
				$form = new Doku_Form ( array ('id' => 'payment' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				if ($orderid != - 1) {
					$form->addHidden ( 'payment', $orderid );
				}
				$form->startFieldSet ( $this->getLang ( 'field_payment' ) );
				$form->addElement ( "<table>" );
				$form->addElement ( "<tr>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_total' ) . "</th><th></th><th></th><th>" . sprintf ( "%.2f &euro;", $dbhelper->fetchPricesum ( $_REQUEST ['orderid'] ) ) . "</th><th></th><th></th></tr>\n" );
				$form->addElement ( "</table>" );
				$form->addElement ( "<input type=\"submit\" value=\"" . $this->getLang ( 'btn_markpaid' ) . "\" />" );
				$form->endFieldSet ();
				$form->printForm ();
			
			}
		}
	}
}

