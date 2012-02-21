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
require_once ('MDB2.php');

class admin_plugin_printservice_printsummary extends DokuWiki_Admin_Plugin {
	
	public function getMenuSort() {
		return FIXME;
	}
	public function forAdminOnly() {
		return true;
	}
	public function getMenuText() {
		return $this->getLang ( 'menu_printsummary' );
	}
	public function handle() {
	}
	
	public function html() {
		ptln ( '<h1>' . $this->getLang ( 'menu_printsummary' ) . '</h1>' );
		
		$this->dbConnect ();
		if (! $res = $this->fetchAllOrders ()) {
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
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_paid' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_status' ) . "</th>" );
			$form->addElement ( "</tr>" );
			while ( $row = $res->fetchRow () ) {
				$form->addElement ( "<tr>" );
				$form->addElement ( "<td><input type=\"radio\" name=\"userselect\" value=\"" . $row ['username'] . "\" /></td>" );
				$form->addElement ( "<td>{$row['id']}</td>" );
				$form->addElement ( "<td>{$row['realname']}</td>" );
				$form->addElement ( "<td>{$row['username']}</td>" );
				$form->addElement ( "<td>{$row['pages']}</td>" );
				$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $row ['price'] ) . "</td>" );
				$form->addElement ( "<td>" . ($row ['paymentstate'] == 'paid' ? 'Ja' : 'Nein') . "</td>" );
				$form->addElement ( "<td>" . $this->getLang ( $row ['deliverystate'] ) . "</td>" );
				$form->addElement ( "</tr>\n" );
			}
			$form->addElement ( "</table>" );
			$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ( 'btn_show' ) ) );
			$form->printForm ();
			$res->free ();
			
			$res = $this->fetchOrderStats ();
			ptln ( "<table>" );
			ptln ( "<tr><th>" . $this->getLang ( 'tbl_pages' ) . "</th><td>{$res['pagesum']}</td></tr>" );
			ptln ( "<tr><th>" . $this->getLang ( 'ordersum' ) . "</th><td>" . sprintf ( "%.2f &euro;", $res ['pricesum'] ) . "</td></tr>" );
			ptln ( "</table>" );
		}
	
	}
	
	private function dbConnect() {
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
	
	private function fetchOrderStats() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT SUM(d.pages) as pagesum, SUM(i.price) as pricesum FROM ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orders o ON o.id = i.order ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'WHERE o.semester=? AND i.deleted=0 ';
		$row = $this->mdb2->extended->getRow ( $sql, null, array ($this->getConf ( 'semester' ) ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query2: " . $row->getMessage () );
		}
		return $row;
	}
	
	private function fetchAllOrders() {
		$sql = 'SELECT p.pf_realname as realname, u.username, sum(d.pages) as pages, sum(i.price) as price, o.id, o.paymentState, o.deliveryState ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'JOIN phpbb_profile_fields_data p ON p.user_id = o.user ';
		$sql .= 'WHERE o.semester=?  AND i.deleted=0 ';
		$sql .= 'GROUP BY o.id';
		$sqldata = $this->getConf ( 'semester' );
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare3: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec3: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty($res)) {
            echo $this->getLang('err_exec');
            return false;
        }
        return $res;
    }
}

// vim:ts=4:sw=4:et:
