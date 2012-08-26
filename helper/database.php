<?php
/**
 * DokuWiki Plugin printservice (Helper Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (! defined ( 'DOKU_INC' )) die ();

if (! defined ( 'DOKU_LF' )) define ( 'DOKU_LF', "\n" );
if (! defined ( 'DOKU_TAB' )) define ( 'DOKU_TAB', "\t" );
if (! defined ( 'DOKU_PLUGIN' )) 
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );
require_once ('MDB2.php');

class helper_plugin_printservice_database extends DokuWiki_Plugin {
	function getInfo() {
		return array (
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2012-02-25',
				'name'   => 'Datenbank-Zugriff',
				'desc'   => 'gemeinsame Funktionen fuer Datenbank-Zugriff',
				'url'    => 'http://www.fs-eit.de'
		);
	}
	
	/*function getMethods() {
		$result = array ();
		$result [] = array (
				'name' => 'getBlog', 
				'desc' => 'returns blog entries in reverse chronological order', 
				'params' => array (
						'namespace' => 'string', 
						'number (optional)' => 'integer' 
				), 
				'return' => array (
						'pages' => 'array' 
				) 
		);
		return $result;
	}*/
	
	// admin/mail
	// admin/printmapping
	// admin/printpay
	// admin/printsummary
	// action/printprocess
	// syntax/listorders
	// syntax/printorder
	function dbConnect() {
		$dsn = 'mysql://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		// echo $dsn."\n<br />";
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $this->mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage () );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		$this->mdb2->setCharset ( 'utf8' );
		return true;
	}
	
	// admin/printmapping
	// admin/printpay
	// admin/printsummary
	// syntax/listorders
	// syntax/printorder
	function fetchSemester($semester) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT name FROM " . $this->getConf ( 'db_prefix' ) . "semesters WHERE code=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($semester ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query1: " . $row->getMessage () );
		}
		return $row;
	}
	
	// admin/printmapping
	// admin/printsummary
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
	
	// admin/printpay
	// syntax/listorders
	function fetchOrderItems($user, $semester) {
		$ids = $this->fetchOrderIds ( $user, $semester );
		
		$sql = 'SELECT d.title, i.format, i.duplex, d.pages, i.price, d.comment, i.id, o.id as orderid, d.filename, o.orderState ';
		// d.pages, o.id nur bei printpay; ORDER BY nur bei listorders
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'WHERE o.id = ? AND i.deleted=0 ';
		$sql .= 'ORDER BY i.id';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare3a: " . $query->getMessage ();
			return false;
		}
		$result = array ();
		foreach ( $ids as $selected ) {
			$res = $query->execute ( $selected );
			if (PEAR::isError ( $res )) {
				echo "Exec3a: " . htmlentities ( $res->getMessage () ) . "<br>\n";
			} elseif ($res == DB_OK or empty ( $res )) {
				echo $this->getLang ( 'err_exec' );
			} else {
				$result [] = $res->fetchAll ();
			}
		}
		$res->free ();
		return $result;
	}
	function fetchOrderItemsByIds($ids) {
		$sql = 'SELECT d.title, i.format, i.duplex, d.pages, i.price, d.comment, i.id, o.id as orderid, d.filename, o.orderState ';
		// d.pages, o.id nur bei printpay; ORDER BY nur bei listorders
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'WHERE o.id = ? AND i.deleted=0 ';
		$sql .= 'ORDER BY i.id';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare3b: " . $query->getMessage ();
			return false;
		}
		if (is_array ( $ids )) {
			$result = array ();
			foreach ( $ids as $selected ) {
				$res = $query->execute ( $selected );
				if (PEAR::isError ( $res )) {
					echo "Exec3b1: " . htmlentities ( $res->getMessage () ) . "<br>\n";
				} elseif ($res == DB_OK or empty ( $res )) {
					echo $this->getLang ( 'err_exec' );
				} else {
					$result [] = $res->fetchAll ();
				}
			}
			$res->free ();
			return $result;
		} else {
			$res = $query->execute ( $ids );
			if (PEAR::isError ( $res )) {
				echo "Exec3b2: " . htmlentities ( $res->getMessage () ) . "<br>\n";
			} elseif ($res == DB_OK or empty ( $res )) {
				echo $this->getLang ( 'err_exec' );
			} else {
				$result = $res->fetchAll ();
			}
			$res->free ();
			return $result;
		}
	
	}
	
	// admin/printmapping
	// admin/printsummary
	function fetchAllOrders() {
		$sql = 'SELECT p.pf_realname as realname, u.username, sum(d.pages) as pages, sum(i.price) as price, o.id, o.orderState ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'JOIN phpbb_profile_fields_data p ON p.user_id = o.user ';
		$sql .= 'WHERE o.semester=?  AND i.deleted=0 ';
		$sql .= 'GROUP BY o.id ';
		$sql .= 'ORDER BY o.orderState ';
		$sqldata = $this->getConf ( 'semester' );
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare4: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec4: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	
	// admin/printpay
	// syntax/listorders
	function fetchPricesum($orderid) {
		$sql = "SELECT sum(i.price) ";
		$sql .= 'FROM skript_orderitems i ';
		$sql .= 'JOIN skript_orders o ON o.id = i.order ';
		$sql .= 'WHERE o.id = ? AND i.deleted=0 ';
		$sqltype = array ('integer' );
		$row = $this->mdb2->extended->getOne ( $sql, null, ( int ) $orderid, $sqltype );
		if (PEAR::isError ( $row )) {
			die ( "Query5: " . $row->getMessage () );
		}
		return $row;
	}
	
	// admin/printpay
	// action/printprocess
	// syntax/listorders
	// syntax/printorder
	function fetchOrderState($user, $semester) {
		$ids = $this->fetchOrderIds ( $user, $semester );
		$this->mdb2->loadModule ( 'Extended', null, false );
		$this->mdb2->loadModule ( 'Manager' );
		$definition = array ('id' => array ('type' => 'integer', 'unsigned' => 1, 'notnull' => 1 ) );
		$create = $this->mdb2->createTable ( 'temp', $definition, array ('temporary' => true ) );
		if (PEAR::isError ( $create )) {
			echo "Create6: " . $create->getMessage () . "<br />\n";
			return false;
		}
		$query = $this->mdb2->prepare ( 'INSERT INTO temp VALUES (?)' );
		if (PEAR::isError ( $query )) {
			echo "Insert6: " . $query->getMessage () . "<br />\n";
			return false;
		}
		$this->mdb2->extended->executeMultiple ( $query, $ids );
		
		$sql = "SELECT o.id, o.orderState FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "WHERE o.id IN (SELECT * FROM temp)";
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare6: " . $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $ids );
		$drop = $this->mdb2->dropTable ( 'temp' );
		if (PEAR::isError ( $drop )) {
			echo "Drop6: " . $drop->getMessage () . "<br />\n";
			return false;
		}
		if (PEAR::isError ( $res )) {
			echo "Exec6: " . htmlentities ( $res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res->numRows () == 0) {
			return 'notfound';
		}
		while ( $row = $res->fetchRow () ) {
			if ($row ['orderstate'] == 'unpaid') {
				return "unpaid";
			}
		}
		return "final";
	}
	function fetchOrderStateById($id) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT o.id, o.orderState FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "WHERE o.id = ?";
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare6b: " . $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $id );
		if (PEAR::isError ( $res )) {
			echo "Exec6b: " . htmlentities ( $res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res->numRows () == 0) {
			return 'notfound';
		}
		$row = $res->fetchRow ();
		return $row ['orderstate'];
	}
	
	// admin/printpay
	// action/printprocess mit user
	function deleteOrders($ids, $user = "EIT-Admin") {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'SET i.deleted = 1 ';
		if($user == "EIT-Admin") {
			$sql .= 'WHERE i.id= :id';
		} else {
			$sql .= 'WHERE i.id= :id AND i.order IN ';
			$sql .= '(SELECT DISTINCT o.id FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
			$sql .= 'JOIN phpbb_users u ON o.user=u.user_id ';
			$sql .= "WHERE u.username = :user )";
		}
		$sqltype = array ('integer', 'text');
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare7: " . $query->getMessage () );
			return false;
		}

		$res = $this->mdb2->extended->executeMultiple ( $query, $ids);
		if (PEAR::isError ( $res )) {
			die ( "Exec7: " . $res->getMessage () .$res->getDebugInfo());
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
			die ( "Query8: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchLecturers() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, name  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'lecturers ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query9: " . $rows->getMessage () );
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
			die ( "Query10: " . $rows->getMessage () );
		}
		return $rows;
	}
	function fetchDocuments() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, filename  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'documents ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query11: " . $rows->getMessage () );
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
			echo "Prepare12: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec12: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
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
			echo "Prepare13: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec13: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
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
			echo "Prepare14: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $mapping );
		if (PEAR::isError ( $res )) {
			die ( "Exec14: " . $res->getMessage () );
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
			echo "Prepare15: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $mapping );
		if (PEAR::isError ( $res )) {
			die ( "Exec15: " . $res->getMessage () );
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
			echo "Prepare16: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec16: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	//admin/printpay
	function searchOrdersByUser($user, $semester) {
		$sql = 'SELECT p.pf_realname as realname, u.username, sum(d.pages) as pages, sum(i.price) as price, o.id, o.orderState ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ON i.order = o.id ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'JOIN phpbb_profile_fields_data p ON p.user_id = o.user ';
		$sql .= 'WHERE o.semester=:semester  AND i.deleted=0 ';
		$sql .= 'AND (u.username REGEXP :user OR p.pf_realname REGEXP :user)';
		$sql .= 'GROUP BY o.id ';
		$sql .= 'ORDER BY o.orderState ';
		
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare17: " . htmlentities ($query->getMessage () ) . "<br>\n";
			return false;
		}
		$res = $query->execute ( array ('user'=> $this->buildSearchRegexp($user), 'semester'=> $semester ) );
		if (PEAR::isError ( $res )) {
			echo "Exec17: " . htmlentities ( $res->getMessage () ) . "<br>\n";
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
		$sql .= "SET orderState='paid' WHERE id=?";
		$sqltype = array ('integer' );
		$res = $query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError($res)) {
			die("Prepare18: ".$res->getMessage());
		}
		$query->execute ( $order );
		if (PEAR::isError($res)) {
			die("Exec18: ".$res->getMessage());
		}
		return true;
	}
	function buildSearchRegexp($needle) {
		 $regexp = "";
		 $split = str_split($needle);
		 foreach ($split as $char) {
		 	if(strtoupper($char)==strtolower($char)) {
		 		$regexp .= $char;
		 	} else {
		 		$regexp .= "[".strtolower($char).strtoupper($char)."]";
		 	}
		}
		return $regexp;
	}
	
	//action/printprocess
	function createOrder($semester, $user) {
		$sql = 'INSERT INTO ' . $this->getConf ( 'db_prefix' ) . 'orders (semester, user) ';
		$sql .= 'VALUES (?,(SELECT user_id FROM phpbb_users WHERE username=?))';
		$sqldata = array ($semester, $user );
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare19: " . $query->getMessage ();
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec19: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function sendOrder($ids, $options) {
		var_dump($options);
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'INSERT INTO `skript_orderitems`(`order`, `file`, `format`, `duplex`, `price`) ';
		$sql .= 'VALUES (:order, :file, :format, :pagemode, :price)';
		$sqltype = array ('integer', 'integer', 'text', 'text', 'decimal' );
	
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg ( "Prepare20: " . $query->getMessage () );
			return false;
		}
		$alldata = array ();
		foreach ( $ids as $value ) {
			$price = $this->fetchPrice ( $value, $this->getConf ( 'pagecost' ), ($_REQUEST ['format'] == 'a4' ? 1 : 0.5) );
			$alldata [] = array_merge(array ('file' => $value, 'price' => $price),$options);
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $alldata );
		if (PEAR::isError ( $res )) {
			die ( "Exec20: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	function fetchOrderId($user, $semester, $orderstate='any') { 
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT id FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "JOIN phpbb_users u ON u.user_id=o.user ";
		$sql .= "WHERE u.username=:user ";
		$sql .= "AND o.semester=:semester ";
		if ($orderstate != 'any') {
			$sql .= "AND o.orderState=:orderstate ";
		}
		$row = $this->mdb2->extended->getOne ( $sql, null, array ('user'=>$user,'semester'=>$semester, 'orderstate'=>$orderstate ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query21: " . $row->getMessage () );
		}
		return $row;
	}
	function fetchPrice($docid, $pagecost, $factor) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT pages FROM " . $this->getConf ( 'db_prefix' ) . "documents d ";
		$sql .= "WHERE id=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($docid ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query22: " . $row->getMessage () );
		}
		$price = round ( (( int ) $row * $pagecost + 5) / 2, - 1 ) / 50 * $factor;
		return $price;
	}

	//syntax/printorder
	function fetchCurrentDocs() {
		$sql = 'SELECT l.course, l.semester, l.name, d.filename, d.title, d.pages, d.comment, d.id, lr.name as lecturer ';
		$sql .= 'FROM `' . $this->getConf ( 'db_prefix' ) . 'mappings` m ';
		$sql .= 'JOIN `' . $this->getConf ( 'db_prefix' ) . 'documents` d ON d.id=m.document ';
		$sql .= 'JOIN `' . $this->getConf ( 'db_prefix' ) . 'lectures` l ON l.id=m.lecture ';
		$sql .= 'JOIN `' . $this->getConf ( 'db_prefix' ) . 'lecturers` lr ON lr.id=m.lecturer ';
		$sql .= 'WHERE m.semester=? ';
		$sql .= 'ORDER BY l.course, l.edv_id, d.filename ';

		$sqldata = $this->getConf ( 'semester' );
		$sqltype = array ('text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare23: " . $query->getMessage () . "<br>\n";
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec23: " . htmlentities ( $this->resOwnOrder->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	
	//helper/database
	function fetchOrderIds($user, $semester) {
		$sql = 'SELECT o.id ';
		//d.pages, o.id nur bei printpay; ORDER BY nur bei listorders
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
		$sql .= 'WHERE u.username = :user AND o.semester= :semester ';
		$sql .= 'ORDER BY o.id';
		$sqltype = array ('text', 'text' );
		$query = $this->mdb2->prepare ( $sql, $sqltype, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query )) {
			echo "Prepare24: ".$query->getMessage ();
			return false;
		}
		$res = $query->execute ( array('user'=>$user, 'semester'=>$semester) );
		if (PEAR::isError ( $res )) {
			echo "Exec24: " . htmlentities ( $res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		$ids = array();
		while ( $row = $res->fetchRow () ) {
			
			$ids[] = $row['id'];
		}
		$res->free ();
		return $ids;
	}
	
	//admin/mail
	function fetchLecturersForSemester($semester) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT DISTINCT l.name, l.gender, l.mail, m.lecturer ';
		$sql .= 'FROM skript_lecturers l ';
		$sql .= 'JOIN skript_mappings m ON m.lecturer = l.id ';
		$sql .= 'JOIN skript_lectures le ON m.lecture = le.id ';
		$sql .= 'WHERE m.semester = ? ';
		$sql .= 'AND l.send_mail = 1 ';
		//echo $dsn."\n";
		//echo "sql25: ".$sql."\n";
		//echo $semester."\n";
		
		$sqltype = array ('text' );
		$rows = $this->mdb2->extended->getAll ( $sql, NULL, array($semester), $sqltype, MDB2_FETCHMODE_ASSOC );
		if (PEAR::isError ( $rows )) {
			die ( "Query25: " . $rows->getMessage () );
		}
		return $rows;
	}
	function prepareLecturesForSemester() {
		$sql_lec = 'SELECT DISTINCT l.edv_id, l.name ';
		$sql_lec .= 'FROM skript_lectures l ';
		$sql_lec .= 'JOIN skript_mappings m ON m.lecture = l.id ';
		$sql_lec .= 'WHERE m.semester = ? ';
		$sql_lec .= 'AND m.lecturer = ? ';
		//echo "sql_lec: ".$sql_lec."\n";
		$sqltype_lec = array ('text', 'integer' );
		$query_lec = & $this->mdb2->prepare ( $sql_lec, $sqltype_lec, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query_lec )) {
			die ( "Prepare26Lec: " . htmlentities ( $query_lec->getMessage () ) );
		}
		return $query_lec;
	}
	function prepareDocumentsForSemester() {
		$sql_doc = 'SELECT DISTINCT d.filename, d.title, d.author, d.update ';
		$sql_doc .= 'FROM skript_documents d ';
		$sql_doc .= 'JOIN skript_mappings m ON m.document = d.id ';
		$sql_doc .= 'WHERE m.semester = ? ';
		$sql_doc .= 'AND m.lecturer = ? ';
		$sql_doc .= 'AND m.document > 3 ';
		//echo "sql_doc: ".$sql_doc."\n";
		$sqltype_doc = array ('text', 'integer' );
		$query_doc = & $this->mdb2->prepare ( $sql_doc, $sqltype_doc, MDB2_PREPARE_RESULT );
		if (PEAR::isError ( $query_doc )) {
			die ( "Prepare27Doc: " . htmlentities ( $query_doc->getMessage () ) );
		}
		return $query_doc;
	}
	function fetchLecturesForSemester($query_lec, $semester, $lecturer) {
		$res_lec = & $query_lec->execute ( array ($semester, $lecturer ) );
		if (PEAR::isError ( $res_lec )) {
			die ( "Exec28Lec: " . htmlentities ( $res_lec->getMessage () ) );
		}
		$lectures = $res_lec->fetchAll ();
		//echo "lectures: ".print_r($lectures,true)."\n";
		$res_lec->free ();
		return $lectures;
	}
	function fetchDocumentsForSemester($query_doc, $semester, $lecturer) {
		$res_doc = & $query_doc->execute ( array ($semester, $lecturer ) );
		if (PEAR::isError ( $res_doc )) {
			die ( "Exec29Doc: " . htmlentities ( $res_doc->getMessage () ) );
		}
		$documents = $res_doc->fetchAll ();
		//echo "documents: ".print_r($documents,true)."\n";
		$res_doc->free ();
		return $documents;
	}
				
				
				
}
