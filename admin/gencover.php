<?php
/*
require ('MDB2.php');

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
$sql = 'SELECT DISTINCT o.id, o.user, p.pf_realname as name, SUM(i.price) AS price ';
$sql .= 'FROM skript_orders o ';
$sql .= 'JOIN skript_orderitems i ON i.order = o.id ';
$sql .= 'JOIN phpbb_profile_fields_data p ON p.user_id = o.user ';
$sql .= 'WHERE o.semester = ? ';
$sql .= 'AND i.deleted = 0 ';
$sql .= "AND o.orderState='paid' ";
$sql .= 'GROUP BY o.id ';
//echo $dsn."\n";
//echo "sql: ".$sql."\n";
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
$sql_item = 'SELECT i.id, d.title, d.pages, i.format, i.duplex, i.price, d.filename, d.id as did ';
$sql_item .= 'FROM `skript_orderitems` i ';
$sql_item .= 'JOIN `skript_orders` o ON o.id = i.order ';
$sql_item .= 'JOIN `skript_documents` d ON d.id = i.file ';
$sql_item .= "WHERE o.orderState = 'paid' ";
$sql_item .= 'AND i.deleted =0 ';
$sql_item .= 'AND o.semester = ? ';
$sql_item .= 'AND o.id = ? ';

echo "sql_item: ".$sql_item."\n";
$sqltype_item = array ('text', 'integer' );
$query_item = & $mdb2->prepare ( $sql_item, $sqltype_item, MDB2_PREPARE_RESULT );
if (PEAR::isError ( $query_item )) {
	die ( "Prepare2Item: " . htmlentities ( $query_item->getMessage () ) );
}

//print_r ( $res );
//echo "rows found: ".$res->numRows()."\n";
//echo "\n==========================================================================\n";

while ( $row = $res->fetchRow () ) {
	//echo "params: ".$semester." - ".$row['id']."\n";
	$res_item = & $query_item->execute ( array ($semester, $row ['id'] ) );
	if (PEAR::isError ( $res_item )) {
		die ( "Exec2Item: " . htmlentities ( $res_item->getMessage () ) );
	}
	$items = $res_item->fetchAll ();
	//echo "items: ".print_r($items,true)."\n";
	$res_item->free ();
	$pages = 0;
	foreach ($items as $value) {
		if($value['duplex']=='duplex' && $value['format']=='a4') {
			$value['pages'] = ceil($value['pages']/2.0);
		} else if($value['duplex']=='duplex' && $value['format']=='a5') {
			$value['pages'] = ceil($value['pages']/4.0);
		} else if($value['duplex']=='simplex' && $value['format']=='a4') {
			$value['pages'] = ceil($value['pages']/2.0);
		} else if($value['duplex']=='simplex' && $value['format']=='a5') {
			//nothing to do here
		}
		$pages += $value['pages'];
	} 
	
	echo "Erstelle Deckblatt {$row['id']} fuer {$row['name']}\n";
	$file = fopen("cover-{$row['id']}.tex","w");
	coverStart($row['id'], $row['name'], $pages);
	coverTable($items);
	coverEnd();
	fclose($file);
	//echo "==========================================================================\n\n\n";
	//print_r ( $row );
	//break;
}
$res->free ();

function coverTable($items) {
	global $file;
	//$highlightIds = array(1,2,3); //array(4,5,6,7,8,43,90,91,44);
	foreach ($items as $value) {
		fwrite($file, "{$value['id']} & {$value['title']}: & ".(str_replace('_','\textunderscore{}',$value['filename']))." & {$value['pages']} & {$value['format']} & {$value['duplex']} \\\\ \n");
	}
}

function coverStart($id, $name, $pages) {
	global $file;
	fwrite($file, '\documentclass[a4paper]{scrartcl}'."\n" );
	fwrite($file, '\usepackage[a4paper,left=20mm,right=20mm, top=2cm, bottom=2cm]{geometry}'."\n" );
	fwrite($file, '\usepackage[utf8]{inputenc}'."\n" );
	fwrite($file, '\usepackage[T1]{fontenc}'."\n" );
	fwrite($file, '\usepackage[pdftex]{graphicx}'."\n" );
	fwrite($file, '\usepackage[ngerman]{babel}'."\n" );
	fwrite($file, '\usepackage{fancyhdr}'."\n" );
	fwrite($file, '\pagestyle{fancy}'."\n" );
	fwrite($file, '\fancyhead{ }'."\n" );
	fwrite($file, '\fancyfoot{ }'."\n" );
	fwrite($file, '\title{\includegraphics[width=5cm]{logo}\\\\ \bigskip  Skriptendruck}'."\n" );
	fwrite($file, '\author{Fachschaft EIT}'."\n" );
	fwrite($file, '\date{18.03.2012}'."\n" );
	fwrite($file, '\renewcommand{\headrulewidth}{0pt}'."\n" );
	fwrite($file, ''."\n" );
	fwrite($file, '\begin{document}'."\n" );
	fwrite($file, '\maketitle'."\n" );
	fwrite($file, '\thispagestyle{fancy}'."\n" );
	fwrite($file, '\section*{Bestellinfo}'."\n" );
	fwrite($file, '\begin{tabular}{ll}'."\n" );
	fwrite($file, '\textbf{ID} & '.$id.' \\\\ '."\n" );
	fwrite($file, '\textbf{Name} & '.$name.' \\\\ '."\n" );
	fwrite($file, '\textbf{Seiten} & '.$pages.'\\\\ '."\n" );
	fwrite($file, '\end{tabular} '."\n" );
	fwrite($file, '\section*{Dokumente}'."\n" );
	fwrite($file, '\begin{tabular}{crlccc}'."\n" );
	fwrite($file, '\textbf{ID} & \textbf{Typ} & \textbf{Datei} & \textbf{Seiten} & \textbf{Format} & \textbf{Seitenmodus}\\\\ \hline'."\n" );
}

function coverEnd() {
	global $file;
	fwrite($file, '\end{tabular} '."\n" );
	fwrite($file, '\newpage'."\n" );
	fwrite($file, '\begin{figure}'."\n" );
	fwrite($file, '\centering'."\n" );
	fwrite($file, '\includegraphics[width=15cm]{outreach}'."\n" );
	fwrite($file, '\caption{http://xkcd.com/585/}'."\n" );
	fwrite($file, 'lizensiert als \includegraphics[height=4ex]{cc_by-nc} (http://creativecommons.org/licenses/by-nc/2.5/'."\n" );
	fwrite($file, '\end{figure}'."\n" );
	fwrite($file, '\end{document}'."\n" );
}
*/
?> 
