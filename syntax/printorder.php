<?php
/**
 * DokuWiki Plugin printservice (Syntax Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'syntax.php';

class syntax_plugin_printservice_printorder extends DokuWiki_Syntax_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Bestellen',
				'desc'   => 'nimmt Bestellungen entgegen',
				'url'    => 'http://www.fs-eit.de',
		);
	}
	
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
		//$dbhelper =& plugin_load('helper','printservice_database');
		//$dbhelper->dbConnect ();
		return array ();
	}
	
	public function render($mode, &$renderer, $data) {
		$renderer->info ['cache'] = false;
		if ($mode != 'xhtml')
			return false;
		
		//DB-Link, Semester abfragen
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		
		if ($this->getConf ( 'active' ) == 0) {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_noorder' ) . "</div></p>";
		} elseif (($temp = $dbhelper->fetchOrderState ( $_SERVER ['REMOTE_USER'], $this->getConf ( 'semester' ) )) == 'notfound') {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_notfound' ) . "</div></p>";
		} elseif ($temp != 'unpaid') {
			$renderer->doc .= "<p><div class=\"noteimportant\">" . $this->getLang ( 'note_orderfinal' ) . "</div></p>";
		} elseif (! $res = $dbhelper->fetchCurrentDocs ()) {
			$renderer->doc .= "<p><div class=\"notewarning\">" . $this->getLang ( 'err_noorder' ) . "</div></p>";
		} else {
			$form = new Doku_Form ( array ('id' => 'neworder' ) );
			$form->addHidden ( 'action', 'order_send' );
			$form->startFieldSet ( $this->getLang ( 'tbl_choosedoc' ) );
			$form->addElement ( "<table><tr>" );
			$form->addElement ( "<th colspan=\"2\">" . $this->getLang ( 'tbl_semester' ) . "</th>" );
			$form->addElement ( "<th>" . $this->getLang ( 'tbl_lecturer' ) . "</th>" );
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
				$form->addElement ( "<td>{$row['lecturer']}</td>" ); //lecturer-name
				$form->addElement ( "<td>{$row['name']}</td>" ); //lecture-name
				$form->addElement ( "<td><a href=\"{$this->getConf ( 'dlpath' )}{$row['filename']}\">{$row['title']}</a></td>" ); //document name
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
}
