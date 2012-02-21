<?php
/**
 * DokuWiki Plugin printservice (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Florian Rinke <rinke.florian@web.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'syntax.php';
require_once ('MDB2.php');

class syntax_plugin_printservice_printorder extends DokuWiki_Syntax_Plugin {
	public function getType() {
		return 'container';
	}
	
	public function getPType() {
		return 'stack';
	}
	
	public function getSort() {
		return 500;
	}
	
	public function connectTo($mode) {
		$this->Lexer->addSpecialPattern ( '<printorder>', $mode, 'plugin_printservice_printorder' );
	}
	
	public function handle($match, $state, $pos, &$handler) {
		$this->dbConnect ();
		return array ();
	}
	
	public function render($mode, &$renderer, $data) {
		$renderer->info ['cache'] = false;
		if ($mode != 'xhtml')
			return false;
		
		//DB-Link, Semester abfragen
		$this->dbConnect ();
		if ($this->getConf ( 'active' ) == 0) {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_noorder' ) . "</div></p>";
		} elseif ($this->fetchOrderState ( $_SERVER ['REMOTE_USER'] ) == 'notfound') {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_notfound' ) . "</div></p>";
		} elseif ($this->fetchOrderState ( $_SERVER ['REMOTE_USER'] ) != 'unpaid') {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_orderfinal' ) . "</div></p>";
		} elseif (! $res = $this->fetchCurrentDocs ()) {
			$renderer->doc .= "<p><div class=\"notewarning\">" . $this->getLang ( 'err_noorder' ) . "</div></p>";
		} else {
			$form = new Doku_Form ( array ('id' => 'neworder' ) );
			$form->addHidden ( 'action', 'order_send' );
			$form->startFieldSet ( $this->getLang ( 'tbl_choosedoc' ) );
			$form->addElement ( "<table><tr>" );
			$form->addElement ( "<th colspan=\"2\">" . $this->getLang ( 'tbl_semester' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_lecture' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_file' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_pages' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_baseprice' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_order' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_comment' ) . "</th>" );
			$form->addElement ( "</tr>" );
			while ( $row = $res->fetchRow () ) {
				$form->addElement ( "<tr>" );
				$form->addElement ( "<td>{$row['course']}</td>" ); //course
				$form->addElement ( "<td>{$row['semester']}</td>" ); //semester
				$form->addElement ( "<td>{$row['name']}</td>" ); //lecture-name
				$form->addElement ( "<td><a href=\"{$row['filename']}\">{$row['title']}</a></td>" ); //document name
				$form->addElement ( "<td>{$row['pages']}</td>" ); //pages
				$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", round ( (( int ) $row ['pages'] * $this->getConf ( 'pagecost' ) + 5) / 2, - 1 ) / 50 ) . "</td>" ); //price
				$form->addElement ( "<td><input type=\"checkbox\" name=\"orderId[]\" value=\"{$row['id']}\" /></td>" ); //order
				$form->addElement ( "<td>{$row['comment']}</td>" ); //comment
				$form->addElement ( "</tr>" );
			}
			$form->addElement ( "</table>" );
			$form->endFieldSet ();
			$form->startFieldSet ( $this->getLang ( 'tbl_chooseformat' ) );
			$form->addElement ( "<input type=\"radio\" name=\"format\" value=\"a4\" checked=\"checked\" /> A4<br />" );
			$form->addElement ( "<input type=\"radio\" name=\"format\" value=\"a5\" /> 2-auf-1" );
			$form->endFieldSet ();
			$form->startFieldSet ( $this->getLang ( 'tbl_choosepagemode' ) );
			$form->addElement ( "<input type=\"radio\" name=\"pagemode\" value=\"simplex\" /> einseitig<br />" );
			$form->addElement ( "<input type=\"radio\" name=\"pagemode\" value=\"duplex\" checked=\"checked\" /> doppelseitig" );
			$form->endFieldSet ();
			$form->startFieldSet ( $this->getLang ( 'tbl_sendorder' ) );
			$form->addElement ( form_makeButton ( 'submit', 'show', $this->getLang ( 'btn_sendorder' ) ) );
			$form->endFieldSet ();
			$renderer->doc .= $form->getForm ();
			$res->free ();
		}
		return true;
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
	
	private function fetchCurrentDocs() {
		$sql = 'SELECT l.course, l.semester, l.name, d.filename, d.title, d.pages, d.comment, d.id FROM `' . $this->getConf ( 'db_prefix' ) . 'mappings` m JOIN `' . $this->getConf ( 'db_prefix' ) . 'documents` d ON d.id=m.document JOIN `' . $this->getConf ( 'db_prefix' ) . 'lectures` l ON l.id=m.lecture WHERE m.semester=?';
		$sqldata = $this->getConf ( 'semester' );
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prep3: " . $query->getMessage () . "<br>\n";
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Query3: " . htmlentities ( $this->resOwnOrder->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
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
