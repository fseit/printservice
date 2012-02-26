<?php
/**
 * DokuWiki Plugin printservice (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once ('MDB2.php');

class helper_plugin_printservice_database extends DokuWiki_Plugin {
	function getInfo() {
        return array(
                'author' => 'Florian Rinke',
                'email'  => 'florian.rinke@fs-eit.de',
                'date'   => '2012-02-25',
                'name'   => 'Datenbank-Zugriff',
                'desc'   => 'gemeinsame Funktionen fuer Datenbank-Zugriff',
                'url'    => 'http://www.fs-eit.de',
                );
    }

    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'getBlog',
                'desc'   => 'returns blog entries in reverse chronological order',
                'params' => array(
                    'namespace' => 'string',
                    'number (optional)' => 'integer'),
                'return' => array('pages' => 'array'),
                );
        return $result;
    }
	
	//admin/printmapping
	//admin/printpay
	//admin/printsummary
	//action/printprocess
	//syntax/listorders
	//syntax/printorder
	 function dbConnect() {
		$dsn = 'mysql://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		//echo $dsn."\n<br />";
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $this->mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage (). ', ' . $this->mdb2->getDebugInfo() );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		$this->mdb2->setCharset('utf8');
		return true;
	}

	//admin/printmapping
	//admin/printpay
	//admin/printsummary
	//syntax/listorders
	//syntax/printorder
	function fetchSemester($semester) {
		//falls Zuweisung nicht möglich -> bei Aufruf defaul-Wert einsetzen
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT name FROM " . $this->getConf ( 'db_prefix' ) . "semesters WHERE code=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($semester ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query1a: " . $row->getMessage () );
		}
		return $row;
	}
	
	//admin/printmapping
	//admin/printsummary
	function fetchOrderStats() {
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
	
	//admin/printpay
	//syntax/listorders verschieden
	 function fetchOrder($user) {
		$sql = 'SELECT d.title, i.format, i.duplex, d.pages, i.price, d.comment, i.id, o.id as orderid, d.filename, o.paymentState ';
		//d.pages, o.id nur bei printpay; ORDER BY nur bei listorders
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'WHERE u.username = ? AND i.deleted=0 ';
		$sql .= 'ORDER BY i.id';
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
		
	//admin/printmapping
	//admin/printsummary
	 function fetchAllOrders() {
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
	
	//admin/printpay
	//syntax/listorders
	 function fetchPricesum($user) {
		$sql = "SELECT sum(i.price) ";
		$sql .= 'FROM skript_orderitems i ';
		$sql .= 'JOIN skript_orders o ON o.id = i.order ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'WHERE o.semester = ? AND u.username = ?  AND i.deleted=0 ';
		$sqltype = array ('text', 'text' );
		$sqldata = array ($this->getConf ( 'semester' ), $user );
		$row = $this->mdb2->extended->getOne ( $sql, null, $sqldata, $sqltype );
		if (PEAR::isError ( $row )) {
			die ( "Query4: " . $row->getMessage () );
		}
		return $row;
	}

	//admin/printpay
	//action/printprocess
	//syntax/listorders
	//syntax/printorder
	function fetchOrderState($user) {
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
	
	//admin/printpay
	//action/printprocess mit user
	function deleteOrders($ids, $user="") {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'SET i.deleted = 1 ';
		if($user == "EIT-Admin") {
			$sql .= 'WHERE i.id= ?';
		} else {
			$sql .= 'WHERE i.id= ? AND i.order IN ';
			$sql .= '(SELECT DISTINCT o.id FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
			$sql .= 'JOIN phpbb_users u ON o.user=u.user_id ';
			$sql .= 'WHERE u.username = ' . $this->mdb2->quote ( $this->mdb2->escape ( $user ) ) . ')';
		}
		$sqltype = array ('integer');
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare3: " . $query->getMessage () );
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec3: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	
	//admin/printmapping
	function fetchAllSemesters() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT code, name FROM " . $this->getConf ( 'db_prefix' ) . "semesters";
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query1b: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchLecturers() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, name  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'lecturers ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3a: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchLectures() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, brief  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'lectures ';
		$sql .= 'ORDER BY id ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3b: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchDocuments() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, filename  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'documents ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3c: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchMappings($semester) {
		$sql = 'SELECT id, lecture, lecturer, document ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'mappings o ';
		$sql .= 'WHERE semester=? ';
		$sql .= 'ORDER BY id ';
		$sqldata = $semester;
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare4a: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec4a: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	function fetchUnknownMappings() {
		$sql = 'SELECT id, lecture, lecturer, document ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'mappings o ';
		$sql .= 'WHERE semester=? AND (document=1 OR lecturer=1) ';
		$sql .= 'ORDER BY id ';
		$sqldata = $this->getConf ( 'semester' );
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare4a: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec4b: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	function addMappings($mapping) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'INSERT INTO `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= '(`semester`, `lecture`, `lecturer`, `document`) ';
		$sql .= 'VALUES (:semester, :lecture , :lecturer, :document) ';
		$sqltype = array ('text', 'integer', 'integer', 'integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare4c: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $mapping );
		if (PEAR::isError ( $res )) {
			die ( "Exec4c: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function editMappings($mapping) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= 'SET `lecturer`=:lecturer,`document`=:document ';
		$sql .= 'WHERE `id`=:id ';
		$sqltype = array ('integer', 'integer', 'integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare4d: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $mapping );
		if (PEAR::isError ( $res )) {
			die ( "Exec4d: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function deleteMappings($ids) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'DELETE FROM `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= 'WHERE `id`=? ';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare4e: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec4e: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	//admin/printpay
	function searchUser($user) {
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
	function storePayment($order) {
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
	}
	
	//action/printprocess
	function createOrder() {
		$sql = 'INSERT INTO ' . $this->getConf ( 'db_prefix' ) . 'orders (semester, user) VALUES (?,(SELECT user_id FROM phpbb_users WHERE username=?))';
		$sqldata = array ($this->getConf ( 'semester' ), $_SERVER ['REMOTE_USER'] );
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare1: " . $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec1: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function sendOrder($ids) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'INSERT INTO `skript_orderitems`(`order`, `file`, `format`, `duplex`, `price`) ';
		$sql .= 'VALUES (:order, :file, :format, :pagemode, :price)';
		$sqltype = array ('integer', 'text', 'text', 'text', 'decimal' );
	
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare3: " . $query->getMessage () );
			return false;
		}
		$alldata = array ();
		foreach ( $ids as $value ) {
			$price = $this->fetchPrice ( $value, $this->getConf ( 'pagecost' ), ($_REQUEST ['format'] == 'a4' ? 1 : 0.5) );
			$alldata [] = array ('file' => $value, 'price' => $price, 'order' => $this->fetchOrderId (), 'format' => ($_REQUEST ['format'] == 'a4' ? 'a4' : 'a5'), 'pagemode' => ($_REQUEST ['pagemode'] == 'duplex' ? 'duplex' : 'simplex') );
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $alldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec2: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function fetchOrderId() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT id FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "JOIN phpbb_users u ON u.user_id=o.user ";
		$sql .= "WHERE u.username=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($_SERVER ['REMOTE_USER'] ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query4: " . $row->getMessage () );
		}
		return $row;
	}
	function fetchPrice($id, $pagecost, $factor) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT pages FROM " . $this->getConf ( 'db_prefix' ) . "documents o ";
		$sql .= "WHERE id=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($id ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query4: " . $row->getMessage () );
		}
		$price = round ( (( int ) $row * $pagecost + 5) / 2, - 1 ) / 50 * $factor;
		return $price;
	}
	function fetchCurrentDocs() {
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
	
}

// vim:ts=4:sw=4:et:
