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

class admin_plugin_printservice_printsummary extends DokuWiki_Admin_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Ueberblick',
				'desc'   => 'Uebersicht der Bestellungen',
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
		return $this->getLang ( 'menu_printsummary' );
	}
	public function handle() {
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printsummary")) {
			return;
		}
	}
	
	public function html() {
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printsummary")) {
			ptln(hsc($this->getLang('msg_notauthorized')));
			return;
		}
		ptln ( '<h1>' . $this->getLang ( 'menu_printsummary' ) . '</h1>' );
		
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		if (! $res = $dbhelper->fetchAllOrders ()) {
			ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'err_nolist' ) . "</div></p>" );
		} else {
			$form = new Doku_Form ( array ('id' => 'selectuser', 'method' => 'POST' ) );
			$form->addHidden ( 'page', 'printservice_printpay' );
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
			$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ( 'btn_show' ) ) );
			$form->printForm ();
			$res->free ();
			
			$res = $dbhelper->fetchOrderStats ();
			ptln ( "<table>" );
			ptln ( "<tr><th></th><th>" . $this->getLang ( 'tbl_pages' ) . "</th><th>" . $this->getLang ( 'ordersum' ) . "</th></tr>" );
			
			$res = $dbhelper->fetchOrderStats ();
			ptln ( "<tr><td>Gesamt</td><td>{$res['pagesum']}</td><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			$res = $dbhelper->fetchOrderStats ( 'unpaid' );
			ptln ( "<tr><td>Bestellt</td><td>{$res['pagesum']}</td><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			$res = $dbhelper->fetchOrderStats ( 'paid' );
                        ptln ( "<tr><td>Bezahlt</td><td>{$res['pagesum']}</td><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			$res = $dbhelper->fetchOrderStats ( 'printed' );
			ptln ( "<tr><td>Gedruckt</td><td>{$res['pagesum']}</td><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			$res = $dbhelper->fetchOrderStats ( 'fetched' );
			ptln ( "<tr><td>Abgeholt</td><td>{$res['pagesum']}</td><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			$res = $dbhelper->fetchOrderStatsLimit();
			ptln ( "<tr><th>Limit</th><td colspan=\"2\">{$res['pagesum']} / {$this->getConf ( 'pagelimit' )} Seiten</td></tr>" );
			ptln ( "</table>" );
		}
	}
}
