<?php
/**
 * DokuWiki Plugin printservice (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Florian Rinke <rinke.florian@web.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'action.php';
require_once('MDB2.php');

class action_plugin_printservice_printprocess extends DokuWiki_Action_Plugin {

    public function register($controller) {
		$controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE', $this, 'handle_printprocess');
		//$controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'handle_printprocess_tpl');
	}
	
	public function handle_printprocess(Doku_Event &$event, $param) {
		if(!isset($_REQUEST['action'])){
     	   return true;
    	}

		if(!in_array($_REQUEST['action'], array('order_create','order_send','order_cancel'))) {
			msg("Unknown Action: ".$_REQUEST['action']);
			return true;
		} else {
			$act=$_REQUEST['action'];
			$this->dbConnect();
		}
		
		if ($act == 'order_create') {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return false;
			}
			$this->createOrder ( $_SERVER['REMOTE_USER'] );
			msg ( "Bestellung wurde angelegt" );
		}
		if ($act == 'order_send') {
			if (is_array ( $_REQUEST ['orderId'] )) {
				if (! checkSecurityToken ()) {
					msg ( "nanana!" );
					return false;
				}
				$add = array ();
				foreach ( $_REQUEST ['orderId'] as $value ) {
					if (is_numeric ( $value ))
						$add [] = ( int ) $value;
				}
				if($this->sendOrder ( $add )) {
					msg ( "Skripte wurden zur Bestellung hinzugefügt");//.implode(", ",$add) );
				}
			}
		}
		if ($act == 'order_cancel') {
			if (is_array ( $_REQUEST ['stornoId'] )) {
				if (! checkSecurityToken ()) {
					msg ( "nanana!" );
					return false;
				}
				$delete = array ();
				foreach ( $_REQUEST ['stornoId'] as $value ) {
					if (is_numeric ( $value ))
						$delete [] = ( int ) $value;
				}
				if ($this->deleteOrders ( $delete )) {
					msg ( $this->getLang ( 'msg_deleted' ));//.implode(", ",$delete) );
				}
			}
			
		}
		//return false;
    }
    
    public function handle_printprocess_tpl(Doku_Event &$event, $param) {
    	//echo "tplunknown:<br />\n".print_r( $_REQUEST ,true)."<br />\n".print_r ( $event,true )."<br />\n";
    	echo "tpl<br />\n";
		$event->stopPropagation ();
		$event->preventDefault ();
		return false;
    }
    
	private function dbConnect() {
    	$dsn = 'mysqli://'.$this->getConf('db_user').':'.$this->getConf('db_password').'@'.$this->getConf('db_server').'/'.$this->getConf('db_database');
        $this->mdb2 =& MDB2::connect($dsn);
        if (PEAR::isError($mdb2)) {
            die("connect: ".$this->mdb2->getMessage());
            //echo $db->getMessage();
        }
        $this->mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
        return true;
    }
    
    private function createOrder($user) {
    	//print_R($ids);
        $this->mdb2->loadModule('Extended', null, false);
		$sql = 'INSERT INTO '.$this->getConf('db_prefix').'orders (semester, user) VALUES (?,?)';
		echo "sql1: ".$sql;
		echo "sqldata1: ".print_r($ids,true);
        $sqltype=array('integer','text');
        $query = $this->mdb2->prepare($sql,$sqltype);
		if (PEAR::isError($query)) {
            echo "Prepare1: ".$query->getMessage();
            return false;
        }
        $res = $this->mdb2->extended->executeMultiple($query,array($this->getConf('semester'),$_SERVER['REMOTE_USER']));
		//print_r($res);
        if (PEAR::isError($res)) {
            die("Exec1: ".$res->getMessage());
        }
        $query->free();
        return true;
    }
    
	private function sendOrder($ids) {
    	//print_R($ids);
		$this->mdb2->loadModule('Extended', null, false);
		$sql = 'INSERT INTO `skript_orderitems`(`order`, `file`, `format`, `duplex`, `price`) ';
		$sql .= 'VALUES (:order, :file, :format, :pagemode, :price)';
		$sqltype=array('integer','text','text','text','decimal');
		//msg("sql3: ".$sql);
		//msg("sqltype3: ".print_r($sqltype,true));
        
        $query = $this->mdb2->prepare($sql,$sqltype);
		if (PEAR::isError($query)) {
            msg("Prepare3: ".$query->getMessage());
            return false;
        }
		$alldata=array();
		foreach ($ids as $value) {
			$price=$this->fetchPrice($value,$this->getConf('pagecost'),($_REQUEST['format']=='a4'?1:0.5));
			//msg("price (".$value."): ".print_r($price,true));
			$alldata[] = array('file'=>$value, 'price'=>$price, 'order'=>$this->fetchOrderId(), 'format'=>($_REQUEST['format']=='a4'?'a4':'a5'), 'pagemode'=>($_REQUEST['pagemode']=='duplex'?'duplex':'simplex'));
		}
		//msg("alldata: ".print_r($alldata,true));
		//echo "alldata: ".print_r($alldata,true);
        $res = $this->mdb2->extended->executeMultiple($query,$alldata);
		//print_r($res);
        if (PEAR::isError($res)) {
            die("Exec2: ".$res->getMessage());
        }
        $query->free();
        return true;
    }
	
	private function deleteOrders($ids) {
		//print_R($ids);
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'SET i.deleted = 1 ';
		$sql .= 'WHERE i.id= ? AND i.order IN ';
		$sql .= '(SELECT DISTINCT o.id FROM ' . $this->getConf ( 'db_prefix' ) . 'orders o ';
		$sql .= 'JOIN phpbb_users u ON o.user=u.user_id ';
		$sql .= 'WHERE u.username = '.$this->mdb2->quote($this->mdb2->escape($_SERVER ['REMOTE_USER'])).')';
		//echo "sql3: " . $sql;
		//echo "sqldata3: " . print_r ( $ids, true );
		//msg("sql3: " . $sql);
		//msg("sqldata3: " . print_r ( $ids, true ));
		$sqltype = array ('integer');
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			msg( "Prepare3: " . $query->getMessage ());
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		//print_r($res);
		if (PEAR::isError ( $res )) {
			die ( "Exec3: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	private function fetchOrderId() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT id FROM " . $this->getConf ( 'db_prefix' ) . "orders o ";
		$sql .= "JOIN phpbb_users u ON u.user_id=o.user ";
		$sql .= "WHERE u.username=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($_SERVER ['REMOTE_USER'] ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query4: " . $res->getMessage () );
		}
		return $row;
	}
	
	private function fetchPrice($id,$pagecost,$factor) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT pages FROM " . $this->getConf ( 'db_prefix' ) . "documents o ";
		$sql .= "WHERE id=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($id ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query4: " . $res->getMessage () );
		}
		//msg("pages:".print_r($row,true));
		$price = round(((int)$row*$pagecost+5)/2,-1)/50*$factor;
		//msg("pricesrc:".print_r($price,true));
		return $price;
	}
	
	/*private function fetchDocinfo($id,$pagecost,$factor) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT filename, pages FROM " . $this->getConf ( 'db_prefix' ) . "documents o ";
		$sql .= "WHERE id=?";
		$row = $this->mdb2->extended->getRow ( $sql, null, array ($id ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query5: " . $res->getMessage () );
		}
		$price = round(((int)$row['pages']*$pagecost+5)/2,-1)/50*$factor;
		$data = array('file'=>$row['filename'], 'price'=>$price);
		return $data;
	}*/
}

// vim:ts=4:sw=4:et:
