<?php
/**
 * DokuWiki Plugin printservice (Admin Component)
 *
 * @author  Florian Rinke <rinke.florian@web.de>
 */

// must be run within Dokuwiki
if (!defined ( 'DOKU_INC' )) die ();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) 
	define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN . 'admin.php';
require_once ('Mail.php');
require_once ('MDB2.php');

class admin_plugin_printservice_printlist extends DokuWiki_Admin_Plugin {
	var $output = "";
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2012-08-24',
				'name'   => 'Mailing',
				'desc'   => 'Erzeugt Drucklisten-/Skripte in verschiedenen Formaten',
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
		return $this->getLang ( 'menu_printlist' );
	}
	public function handle() {
		//error_reporting(E_ALL);
		//$this->output .= print_r($_REQUEST, true);
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printlist")) {
			$this->output .= hsc($this->getLang('msg_notauthorized'));
			return;
		}
		$dbhelper = & plugin_load ( 'helper', 'printservice_database' );
		$dbhelper->dbConnect ();
		
		if (isset($_REQUEST['listtype'])) {
			switch ($_REQUEST['listtype']) {
				case "script-peruser":
				break;
				case "script-perdoc":
				break;
				case "list-peruser":
				break;
				case "list-perdoc":
				break;
				default:
				break;
			};
		}
		
	}
	
	public function html() {
		if($this->output != "") {
			ptln($this->output);
			//ptln(print_r($_REQUEST, true));
		} 
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("printlist")) {
			ptln(hsc($this->getLang('msg_notauthorized')));
			return;
		}
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		
		ptln ( '<h1>' . $this->getLang ( 'menu_printlist' ) . '</h1>' );
		
		if (isset($_REQUEST['listtype'])) {
			switch ($_REQUEST['listtype']) {
				case "script-peruser":
					//get customers
					$customers = $dbhelper->fetchCustomers($this->getConf ( 'semester' ));
					
					//prepare items
					$order_query = $dbhelper->prepareOrdersForCustomer();
					
					//echo "rows found: ".$res->numRows()."\n";
					//echo "\n==========================================================================\n";
					$form = new Doku_Form ( array ('id' => 'script-peruser', 'method' => 'POST' ) );
					$form->printForm ();
					$output = $this->bashlistStart();
					foreach($customers as $current) {
					//while ( $row = $res->fetchRow () ) {
						//var_dump($current);
						//echo "params: ".$semester." - ".$current['id']." - ".$current['user']."\n";
						
						//$res_item = & $query_item->execute ( array ($semester, $current ['id'] ) );
						//if (PEAR::isError ( $res_item )) {
						//	die ( "Exec2Item: " . htmlentities ( $res_item->getMessage () ) );
						//}
						//$items = $res_item->fetchAll ();
						$items = $dbhelper->fetchOrdersForCustomer ($order_query, $this->getConf ( 'semester' ), $current['id']);
						//echo "items: ".print_r($items,true)."\n";
						//$res_item->free ();
						$output .= $this->bashlist($current['id'], $current['name'], $items);
						//echo "==========================================================================\n\n\n";
						//print_r ( $current );
						//break;
					}
					$form->addElement ( '<textarea cols="120" rows="25" name="script-peruser-text">'.$output.'</textarea>'."<br />\n");
					$form->printForm ();
				break;
				case "script-perdoc":
				break;
				case "list-peruser":
				break;
				case "list-perdoc":
				break;
				default:
				break;
			};
		} else {
		
			$form = new Doku_Form ( array ('id' => 'list_settings', 'method' => 'POST' ) );
			$form->addHidden ( 'page', 'printservice_printlist' );
			
			$form->startFieldSet ( $this->getLang ( 'field_listtype' ) );
			$form->addElement ( '<input type="radio" id="type-script-peruser" name="listtype" value="script-peruser" checked="checked" /> <label for="type-script-peruser">Bash-Script, Benutzerweise</label>'."<br />\n");
			$form->addElement ( '<input type="radio" id="type-script-perdoc" name="listtype" value="script-perdoc" /> <label for="type-script-perdoc">Bash-Script, Dokumentenweise</label>'."<br />\n");
			$form->addElement ( '<input type="radio" id="type-list-peruser" name="listtype" value="list-peruser" /> <label for="type-list-peruser">Liste, Benutzerweise</label>'."<br />\n");
			$form->addElement ( '<input type="radio" id="type-list-perdoc" name="listtype" value="list-perdoc" /> <label for="type-list-perdoc">Liste, Dokumentenweise</label>'."<br />\n");
			$form->endFieldSet ();
			
			$form->startFieldSet ( $this->getLang ( 'field_listaction' ) );
			$form->addElement ( '<input type="checkbox" id="action-markprinted" name="action" value="markprinted" /> <label for="action-markprinted">Als gedruckt markieren</label>')."<br />\n";
			$form->endFieldSet ();
			
			$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ( 'btn_generatelist' ) ) );
			$form->printForm ();
		}
	}
	
	function bashlistStart() {
		$output = '#!/bin/bash'."\n";
		$output .= '#set -x'."\n";
		$output .= 'printer() { lpr $@ || echo Fehler bei: $@ }'."\n";
		$output .= 'set -e'."\n";
		$output .= 'TOOL="echo" #lpr'."\n";
		$output .= 'TRAY_COVER="Color"'."\n";
		$output .= 'TRAY_DOC="Plain"'."\n";
		$output .= 'PARAM_DEFAULT="-o AccountLogin=Custom.extern  -o AccountPassword=Custom. "'."\n";
		$output .= 'PARAM_STAPLE="-o ARStaple=Staple5 "'."\n";
		$output .= 'PARAM_COVER="-o MediaType=${TRAY_COVER}"'."\n";
		$output .= 'PARAM_DOCS="-o MediaType=${TRAY_DOC}"'."\n";
		$output .= 'PARAM_A4=""'."\n";
		$output .= 'PARAM_A5=""'."\n";
		$output .= 'PARAM_SIMPLEX="-o Duplex=None "'."\n";
		$output .= 'PARAM_DUPLEX_A4="-o Duplex=DuplexNoTumble "'."\n";
		$output .= 'PARAM_DUPLEX_A5="-o Duplex=DuplexTumble -o landscape"'."\n\n";
		return $output;
	}
	function bashlist($id, $name, $items) {
		$output = "\n#### Skripte fuer $id - $name ####\n";
		$output .= "#sleep 5\n";
		$output .= '$TOOL $PARAM_DEFAULT $PARAM_COVER $PARAM_A4 $PARAM_DUPLEX cover/cover-'.$id.".pdf\n";
		foreach ( $items as $value ) {
			$output .= '$TOOL $PARAM_DEFAULT ';
			if($value['duplex']=="duplex") {
				if($value['format']=="a4") {
					$output .= '$PARAM_DUPLEX_A4 ';
				} else {
					$output .= '$PARAM_DUPLEX_A5 ';
				}
			} else {
				$output .= '$PARAM_SIMPLEX ';
			}
			if($value['duplex']=='duplex' && $value['format']=='a4' && $value['pages']<=100) {
				$output .= '$PARAM_STAPLE ';
			} else if($value['duplex']=='duplex' && $value['format']=='a5' && $value['pages']<=200) {
				$output .= '$PARAM_STAPLE ';
			} else if($value['duplex']=='simplex' && $value['format']=='a4' && $value['pages']<=50) {
				$output .= '$PARAM_STAPLE ';
			} else if($value['duplex']=='simplex' && $value['format']=='a5' && $value['pages']<=100) {
				$output .= '$PARAM_STAPLE ';
			}
			$output .= 'pdf/';
			if($value['format']=="a4") {
				$output .= $value['filename'];
			} else {
				$output .= substr($value['filename'],0,-4)."-nup.pdf";
			}
			$output .= "\n";
		}
		return $output;
	}
	
}

/*require ('MDB2.php');

//config 
$user = 'fs_website';
$pass = 'zCsBZvrCEYExecwv';
$dbname = 'fs_website';
$semester = '12ss';
date_default_timezone_set ( "Europe/Berlin" );

//fetch
$dsn = 'mysql://' . $user . ':' . $pass . '@localhost/' . $dbname;
//$mdb2 = MDB2::connect ( $dsn );
$mdb2 = & MDB2::factory ( $dsn );
if (PEAR::isError ( $mdb2 )) {
	die ( "connect: " . $mdb2->getMessage () );
}
$mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
$mdb2->setCharset ( 'utf8' );
//$mdb2->connect();


//get customers
$sql = 'SELECT DISTINCT o.id, o.user, p.pf_realname as name ';
$sql .= 'FROM skript_orders o ';
$sql .= 'JOIN skript_orderitems i ON i.order = o.id ';
$sql .= 'JOIN phpbb_profile_fields_data p ON p.user_id = o.user ';
$sql .= 'WHERE o.semester = ? ';
$sql .= 'AND i.deleted = 0 ';
$sql .= "AND o.orderState='paid' ";
$sql .= 'ORDER BY SUBSTRING_INDEX( name, " ", -1 ) ';
//echo $dsn."\n";
echo "sql: ".$sql."\n";
//echo $semester."\n";


$sqltype = array ('text' );
$query = & $mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
if (PEAR::isError ( $query )) {
	die ( "Prepare1List: " . htmlentities ( $query->getMessage () ) );
}

$res = & $query->execute ( $semester );
if (PEAR::isError ( $res )) {
	die ( "Exec1List: " . htmlentities ( $res->getMessage () ) );
}

//prepare items
$sql_item = 'SELECT o.id, d.title, d.pages, i.format, i.duplex, i.price, d.filename ';
$sql_item .= 'FROM `skript_orderitems` i ';
$sql_item .= 'JOIN `skript_orders` o ON o.id = i.order ';
$sql_item .= 'JOIN `skript_documents` d ON d.id = i.file ';
$sql_item .= "WHERE o.orderState = 'paid' ";
$sql_item .= 'AND i.deleted =0 ';
$sql_item .= 'AND o.semester = ? ';
$sql_item .= 'AND o.id = ? ';
$sql_item .= 'AND NOT i.file <=3 ';
#$sql_item .= 'AND NOT i.file <=8 AND NOT i.file=43 AND NOT i.file =90 AND NOT i.file =91 AND NOT i.file =44 ';

//echo "sql_item: ".$sql_item."\n";
$sqltype_item = array ('text', 'integer' );
$query_item = & $mdb2->prepare ( $sql_item, $sqltype_item, MDB2_PREPARE_RESULT );
if (PEAR::isError ( $query_item )) {
	die ( "Prepare2Item: " . htmlentities ( $query_item->getMessage () ) );
}

//print_r ( $res );
//echo "rows found: ".$res->numRows()."\n";
//echo "\n==========================================================================\n";
$file = fopen("print.sh","w");
bashlistStart();
while ( $row = $res->fetchRow () ) {
	//var_dump($row);
	//echo "params: ".$semester." - ".$row['id']." - ".$row['user']."\n";
	$res_item = & $query_item->execute ( array ($semester, $row ['id'] ) );
	if (PEAR::isError ( $res_item )) {
		die ( "Exec2Item: " . htmlentities ( $res_item->getMessage () ) );
	}
	$items = $res_item->fetchAll ();
	//echo "items: ".print_r($items,true)."\n";
	$res_item->free ();
	bashlist($row['id'], $row['name'], $items);
	//echo "==========================================================================\n\n\n";
	//print_r ( $row );
	//break;
}
$res->free ();
fclose($file);



}


*/
?> 
