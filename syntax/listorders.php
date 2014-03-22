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

class syntax_plugin_printservice_listorders extends DokuWiki_Syntax_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Anzeige',
				'desc'   => 'Anzeigen/Loeschen von Bestellungen',
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
		$this->Lexer->addSpecialPattern ( '<listorders>', $mode, 'plugin_printservice_listorders' );
	}
	
	public function handle($match, $state, $pos, &$handler) {
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		return array ('semester' => $dbhelper->fetchSemester ($this->getConf ( 'semester' )) );
	}
	
	public function render($mode, &$renderer, $data) {
		$renderer->info ['cache'] = false;
		if ($mode != 'xhtml')
			return false;
		
		//DB-Link, Semester abfragen
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		$orderId = array();
		if (! $state = $dbhelper->fetchOrderState ( $_SERVER ['REMOTE_USER'], $this->getConf ( 'semester' ) )) {
			$renderer->doc .= "<p><div class=\"notewarning\">" . $this->getLang ( 'err_noshow' ) . "</div></p>";
		} else {
			if (($state == 'notfound' ||$state == 'final'  )  && $this->getConf ( 'active' ) == 1) {
				$form = new Doku_Form ( array ('id' => 'myorders' ) );
				$form->addHidden ( 'action', 'order_create' );
				$form->startFieldSet ( $this->getLang ( 'yourorder' ) . $data ['semester'] );
				$form->addElement ( form_makeButton ( 'submit', 'show', $this->getLang ( 'btn_create' ) ) );
				$form->endFieldSet ();
				$renderer->doc .= $form->getForm ();
			}
			if ($state == 'notfound' ||  $this->getConf ( 'active' ) == 0) {
				return false;
			}
			$orderids = $dbhelper->fetchOrderIds ( $_SERVER ['REMOTE_USER'], $this->getConf ( 'semester' ));
			foreach ( $orderids as $element ) {
				$closed = ($this->getConf ( 'active' ) == 0 || $dbhelper->fetchOrderStateById($element) != 'unpaid' );
				$order=$dbhelper->fetchOrderItemsByIds ( $element );
				$form = new Doku_Form ( array ('id' => 'myorders' ) );
				$form->addHidden ( 'action', 'order_cancel' );
				$form->addHidden ( 'orderID', $element );
				$form->startFieldSet ( $this->getLang ( 'field_yourorder' ) . $data ['semester'] ." (#{$element})");
				$form->addElement ( "<table>\n<tr>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_doc' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_format' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_pagemode' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_price' ) . "</th>" );
				if (! $closed)
					$form->addElement ( "<th>" . $this->getLang ( 'tbl_cancel' ) . "</th>" );
				$form->addElement ( "<th>" . $this->getLang ( 'tbl_comment' ) . "</th>" );
				$form->addElement ( "</tr>" );
				foreach ( $order as $selected ) {
					$form->addElement ( "<tr>" );
					$form->addElement ( "<td><a href=\"{$this->getConf ( 'dlpath' )}{$selected['filename']}\">{$selected['title']}: {$selected['filename']}</a></td>" ); // Skript
					$form->addElement ( "<td>" . ($selected ['format'] == "a4" ? $this->getLang ( 'tbl_a4' ) : $this->getLang ( 'tbl_a5' )) . "</td>" ); // Format
					$form->addElement ( "<td>" . ($selected ['duplex'] == "simplex" ? $this->getLang ( 'tbl_simplex' ) : $this->getLang ( 'tbl_duplex' )) . "</td>" ); // Doppelseitig
					$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $selected ['price'] ) . "</td>" ); // Preis
					if (! $closed)
						$form->addElement ( "<td><input type=\"checkbox\" name=\"stornoId[]\" value=\"{$selected['id']}\" /></td>" ); // Stornieren
					$form->addElement ( "<td>{$selected['comment']}</td>" ); // Hinweis
					$form->addElement ( "</tr>\n" );
				}
				$form->addElement ( "<tr>" );
				$form->addElement ( "<th>Gesamt</th><th></th><th></th><th>" . sprintf ( "%.2f &euro;", $dbhelper->fetchPricesum ( $element ) ) . "</th><th></th><th></th></tr>\n" );
				$form->addElement ( "</table>" );
				if ( $closed) {
					$form->addElement ( "<input type=\"reset\" disabled=\"disabled\" value=\"" . $this->getLang ( 'btn_closed' ) . "\" />" );
				} else {
					$form->addElement ( form_makeButton ( 'submit', 'show', $this->getLang ( 'btn_cancel' ) ) );
				}
				$form->endFieldSet ();
				$renderer->doc .= $form->getForm ();
			}
		}
		
		return true;
	}
}
