<?php
/**
 * DokuWiki Plugin printservice (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Florian Rinke <rinke.florian@web.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'admin.php';
require_once('MDB2.php');

class admin_plugin_printservice_printpay extends DokuWiki_Admin_Plugin {

    public function getMenuSort() { return FIXME; }
    public function forAdminOnly() { return false; }
    public function getMenuText() { return $this->getLang('menu_printpay'); }
    public function handle() {
    	//print_r($_REQUEST);
    	$this->dbConnect ();
		
		//delete entries
		if (is_array ( $_REQUEST ['stornoId'] )) {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return;
			}
			$delete = array ();
			foreach ( $_REQUEST ['stornoId'] as $value ) {
				if (is_numeric ( $value ))
					$delete [] = ( int ) $value;
			}
			$this->deleteOrders ( $delete );
			msg ( $this->getLang ( 'msg_deleted' ) );
		}
		
		//store payment
		if (isset ( $_REQUEST ['payment'] )) {
			if (! checkSecurityToken ()) {
				msg ( "nanana!" );
				return;
			}
			//echo "pm_" . $_REQUEST ['payment'] . "_pm";
			$this->storePayment ( $_REQUEST ['payment']+0 );
			msg ( "Bezahlung abgeschlossen" );
		}
	
	}

    public function html() {
    	$this->dbConnect();
    	
        ptln('<h1>' . $this->getLang('menu_printpay') . '</h1>');
        //Search field
        $form = new Doku_Form ( array ('id' => 'namesearch', 'method' => 'POST' ) );
        //$form->addHidden('do','admin');
        $form->addHidden('page','printservice_printpay');
        //$form->addHidden('id',hsc($INFO['id']));
        $form->addElement(form_makeTextField('namesearch', hsc($_REQUEST['namesearch']),'Name:'));
        $form->addElement(form_makeButton('submit', 'admin', $value='Suchen'));
        $form->printForm();
        
        //Select field
        //print_r($_REQUEST);
        if(isset($_REQUEST['namesearch'])) {
    		$names = $this->searchUser($_REQUEST['namesearch']);
    		$form = new Doku_Form ( array ('id' => 'selectuser', 'method' => 'POST' ) );
        	$form->addHidden('page','printservice_printpay');
        	$form->startFieldSet($this->getLang('field_foundusers'));
        	$form->addElement("<table><tr><th></th><th>".$this->getLang('tbl_realname')."</th><th>".$this->getLang('tbl_username')."</th></tr>");
        	while ( $row = $names->fetchRow () ) {
        		$form->addElement("<tr><td><input type=\"radio\" name=\"userselect\" value=\"".$row['username']."\" /></td><td>".$row['realname']."</td><td>".$row['username']."</td></tr>");
        	}
        	$form->addElement("</table>");
        	$form->addElement(form_makeButton('submit', 'admin', $value= $this->getLang('btn_choose') ) );
			$form->endFieldSet ();
			$form->printForm ();
			$names->free ();
		}
		//Bestellung anzeigen
		if (isset ( $_REQUEST ['userselect'] )) {
			$state = $this->fetchOrderState ( $_REQUEST ['userselect'] );
			//echo "st_".$state."_st";
			if (!state) {
				ptln ( "<p><div class=\"notewarning\">".$this->getLang('note_noorder')."</div></p>" );
			} else if ($state != 'unpaid') {
				ptln ( "<p><div class=\"notewarning\">".$this->getLang('note_paid')."</div></p>" );
			} else {
				$orderid=-1;
				$res = $this->fetchOrder ( $_REQUEST ['userselect'] );
				$form = new Doku_Form ( array ('id' => 'myorders' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				$form->addHidden ( 'userselect', hsc($_REQUEST ['userselect']));
				$form->startFieldSet ("Bestellung von ".$_REQUEST ['userselect']." im " .$this->fetchSemester() );
				$form->addElement ( "<table>\n<tr>" );
				$form->addElement ( "<th>".$this->getLang('tbl_doc')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_format')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_pagemode')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_pages')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_price')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_storno')."</th>" );
				$form->addElement ( "<th>".$this->getLang('tbl_comment')."</th>" );
				$form->addElement ( "</tr>" );
				while ( $row = $res->fetchRow () ) {
					//f.title, o.format, o.duplex, o.price, f.comment, o.id, f.filename, o.paid
					$form->addElement ( "<tr>" );
					$form->addElement ( "<td><a href=\"{$row['filename']}\">{$row['title']}</a></td>" ); //Skript
					$form->addElement ( "<td>" . ($row ['format'] == "a4" ? $this->getLang ( 'tbl_a4' ) : $this->getLang ( 'tbl_a5' )) . "</td>" ); //Format
					$form->addElement ( "<td>" . ($row ['duplex'] == "simplex" ? $this->getLang ( 'tbl_simplex' ) : $this->getLang ( 'tbl_duplex' )) . "</td>" ); //Doppelseitig
					$form->addElement ( "<td>{$row['pages']}</td>" ); //Preis
					$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $row ['price'] ) . "</td>" ); //Preis
					$form->addElement ( "<td><input type=\"checkbox\" name=\"stornoId[]\" value=\"{$row['id']}\" /></td>" ); //Stornieren
					$form->addElement ( "<td>{$row['comment']}</td>" ); //Hinweis
					$form->addElement ( "</tr>\n" );
					$orderid=$row['orderid'];
				}
				$form->addElement ( "</table>" );
				$form->addElement("<input type=\"submit\" value=\"".$this->getLang('btn_cancel')."\" />");
				$form->endFieldSet ();
				$form->printForm ();
				$res->free ();
				
				$form = new Doku_Form ( array ('id' => 'payment' ) );
				$form->addHidden ( 'page', 'printservice_printpay' );
				if ($orderid!=-1) {
					$form->addHidden ( 'payment', $orderid );
					
				}
				$form->startFieldSet ($this->getLang('field_payment'));
				$form->addElement ( "<table>" );
				$form->addElement ( "<tr>" );
				$form->addElement ( "<th>".$this->getLang('tbl_total')."</th><th></th><th></th><th>" . sprintf ( "%.2f &euro;", $this->fetchPricesum ( $_REQUEST ['userselect'] ) ) . "</th><th></th><th></th></tr>\n" );
				$form->addElement ( "</table>" );
				$form->addElement ( "<input type=\"submit\" value=\"".$this->getLang('btn_markpaid')."\" />" );
				$form->endFieldSet ();
				$form->printForm ();
				
			}
		}
        
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

    private function fetchSemester() {
        $this->mdb2->loadModule('Extended', null, false);
		$sql="SELECT name FROM ".$this->getConf('db_prefix')."semesters WHERE code=?";
        $row = $this->mdb2->extended->getOne($sql, null, array($this->getConf('semester')), array('text'));
        if (PEAR::isError($res)) {
            die("Query1: ".$res->getMessage());
        }
        return $row;
    }
    
    private function fetchOrder($user) {
		//$sql='SELECT d.title, o.format, o.duplex, o.price, d.comment, o.id, d.filename, o.paid FROM '.$this->getConf('db_prefix').'orders o JOIN '.$this->getConf('db_prefix').'documents d ON d.id = o.file JOIN phpbb_users u ON u.user_id = o.user WHERE u.username=?';
        $sql = 'SELECT d.title, i.format, i.duplex, d.pages, i.price, d.comment, i.id, o.id as orderid, d.filename, o.paymentState ';
        $sql .= 'FROM '.$this->getConf('db_prefix').'orders o ';
        $sql .= 'JOIN '.$this->getConf('db_prefix').'orderitems i ON i.order = o.id ';
        $sql .= 'JOIN '.$this->getConf('db_prefix').'documents d ON d.id = i.file ';
        $sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
        $sql .= 'WHERE u.username = ?';
    	$sqltype=array('text');
        //echo "sql2: ". htmlentities($sql)."<br>\n";
        //echo "sqldata2: ". htmlentities($sqldata)."<br>\n";
        $query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
    	if (PEAR::isError($query)) {
            echo $query->getMessage();
            return false;
        }
		$res = $query->execute($user);
        if (PEAR::isError($res)) {
        	echo "Query2: ".htmlentities($this->resOwnOrder->getMessage())."<br>\n";
            return false;
        } elseif ($res == DB_OK or empty($res)) {
            echo $this->getLang('err_exec');
            return false;
        }
        return $res;
    }
    
    private function searchUser($user) {
		$sql='SELECT u.username, p.pf_realname as realname FROM phpbb_users u JOIN phpbb_profile_fields_data p ON p.user_id=u.user_id WHERE u.username REGEXP ? OR p.pf_realname REGEXP ?';
        $sqltype=array('text','text');
        //echo "sql3: ". htmlentities($sql)."<br>\n";
        //echo "sqldata3: ". htmlentities($sqldata)."<br>\n";
        $query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
    	if (PEAR::isError($query)) {
            echo $query->getMessage();
            return false;
        }
		$res = $query->execute(array($user,$user));
        if (PEAR::isError($res)) {
        	echo "Query3: ".htmlentities($this->resOwnOrder->getMessage())."<br>\n";
            return false;
        } elseif ($res == DB_OK or empty($res)) {
            echo $this->getLang('err_exec');
            return false;
        }
        return $res;
    }

    private function fetchPricesum($user) {
		//$sql='SELECT d.title, o.format, o.duplex, o.price, d.comment, o.id, d.filename, o.paid FROM '.$this->getConf('db_prefix').'orders o JOIN '.$this->getConf('db_prefix').'documents d ON d.id = o.file JOIN phpbb_users u ON u.user_id = o.user WHERE u.username=?';
        $sql = "SELECT sum(i.price) ";
    	$sql .= 'FROM skript_orderitems i ';
        $sql .= 'JOIN skript_orders o ON o.id = i.order ';
        $sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
        $sql .= 'WHERE o.semester = ? AND u.username = ? ';
    	$sqltype=array('text','text');
    	$sqldata=array($this->getConf('semester'),$user);
        //echo "sql4: ". htmlentities($sql)."<br>\n";
        //echo "sqldata4: ". htmlentities(print_r($sqldata,true))."<br>\n";
        $row = $this->mdb2->extended->getOne($sql, null, $sqldata, $sqltype);
        if (PEAR::isError($res)) {
            die("Query4: ".$res->getMessage());
        }
        return $row;
    }
    
	private function fetchOrderState($user) {
        $this->mdb2->loadModule('Extended', null, false);
		$sql="SELECT paymentState, deliveryState FROM ".$this->getConf('db_prefix')."orders o JOIN phpbb_users u ON u.user_id=o.user WHERE u.username=?";
        $sqltype=array('text');
		//echo "sql3: ". htmlentities($sql)."<br>\n";
		$query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
    	if (PEAR::isError($query)) {
            echo $query->getMessage();
            return false;
        }
		$res = $query->execute($user);
        if (PEAR::isError($res)) {
        	echo "Query3: ".htmlentities($this->res->getMessage())."<br>\n";
            return false;
        } elseif ($res == DB_OK or empty($res)) {
        	echo "notfound";
            return 'notfound';
        }
        $row=$res->fetchRow();
        $res->free();
        //echo "ds: ".$row->numRows()." ds";
        //print_r($row);
        if ($row['paymentstate']=='unpaid') {
        	return "unpaid";
        } else return $row['deliverystate'];
    }
    
	private function deleteOrders($ids) {
		//print_R($ids);
        $this->mdb2->loadModule('Extended', null, false);
		$sql="DELETE FROM `".$this->getConf('db_prefix')."orderitems` WHERE id=?";
		echo "sql5: ".$sql;
		echo "sqldata5: ".print_r($ids,true);
        $sqltype=array('integer');
        $query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
		if (PEAR::isError($query)) {
            echo "Prepare5: ".$query->getMessage();
            return false;
        }
        $res = $this->mdb2->extended->executeMultiple($query,$ids);
		//print_r($res);
        if (PEAR::isError($res)) {
            die("Exec5: ".$res->getMessage());
        }
        $query->free();
        return true;
    }

    private function storePayment($order) {
    	//echo "or_".$order."_or";
    	$this->mdb2->loadModule('Extended', null, false);
    	$sql="UPDATE `".$this->getConf('db_prefix')."orders` SET paymentState='paid' WHERE id=?";
        $sqltype=array('integer');
        //cho "sql6: ".$sql;
        $res = $query = $this->mdb2->prepare($sql,$sqltype);
        $query->execute($order);
		//echo "res: ".print_r($res,true);
        if (PEAR::isError($res)) {
            die("Query6: ".$res->getMessage());
        }
        return true;
    }
}

// vim:ts=4:sw=4:et:
