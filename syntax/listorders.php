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
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';
require_once('MDB2.php');

class syntax_plugin_printservice_listorders extends DokuWiki_Syntax_Plugin {
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
        $this->Lexer->addSpecialPattern('<listorders>',$mode,'plugin_printservice_listorders');
//        $this->Lexer->addEntryPattern('<FIXME>',$mode,'plugin_printservice_printorder');
    }

//    public function postConnect() {
//        $this->Lexer->addExitPattern('</FIXME>','plugin_printservice_printorder');
//    }

    public function handle($match, $state, $pos, &$handler){
    	$this->dbConnect();
		return array('semester' => $this->fetchSemester());
    }

    public function render($mode, &$renderer, $data) {
        $renderer->info['cache'] = false;
        if($mode != 'xhtml') return false;

        //DB-Link, Semester abfragen
        $this->dbConnect();
		if(!$res=$this->fetchOrder($_SERVER['REMOTE_USER'])) {
			$renderer->doc .= "<p><div class=\"notewarning\">".$this->getLang('err_noshow')."</div></p>";
		} else {
			$state=$this->fetchOrderState($_SERVER['REMOTE_USER']);
			//echo "st_".$state."_st";
			if($state=='notfound') {
				$form=new Doku_Form(array('id'=>'myorders'));
				$form->addHidden('action','order_create');
				$form->startFieldSet($this->getLang('yourorder').$data['semester']);
				//$form->addElement("<input type=\"submit\" value=\"".$this->getLang('btn_create')."\" />");
				$form->addElement(form_makeButton('submit', 'show', $this->getLang('btn_create')));
				$form->endFieldSet();
				$renderer->doc .= $form->getForm();
			} 
			$closed = ($state=='unpaid' ? false : true);
			$form=new Doku_Form(array('id'=>'myorders'));
			$form->addHidden('action','order_cancel');
			$form->startFieldSet($this->getLang('field_yourorder').$data['semester']);
			$form->addElement("<table>\n<tr>");
			$form->addElement("<th>".$this->getLang('tbl_doc')."</th>");
			$form->addElement("<th>".$this->getLang('tbl_format')."</th>");
			$form->addElement("<th>".$this->getLang('tbl_pagemode')."</th>");
			$form->addElement("<th>".$this->getLang('tbl_price')."</th>");
			if(!$closed) $form->addElement("<th>".$this->getLang('tbl_cancel')."</th>");
			$form->addElement("<th>".$this->getLang('tbl_comment')."</th>");
			$form->addElement("</tr>");
			while ( $row = $res->fetchRow () ) {
				//f.title, o.format, o.duplex, o.price, f.comment, o.id, f.filename, o.paid
				$form->addElement("<tr>");
				$form->addElement("<td><a href=\"{$row['filename']}\">{$row['title']}</a></td>"); //Skript
				$form->addElement("<td>" . ($row ['format'] == "a4" ? $this->getLang('tbl_a4') : $this->getLang('tbl_a5')) . "</td>"); //Format
				$form->addElement("<td>" . ($row ['duplex'] == "simplex" ? $this->getLang('tbl_simplex') : $this->getLang('tbl_duplex')) . "</td>"); //Doppelseitig
				$form->addElement("<td>".sprintf("%.2f &euro;",$row['price'])."</td>"); //Preis
				if(!$closed) $form->addElement("<td><input type=\"checkbox\" name=\"stornoId[]\" value=\"{$row['id']}\" /></td>"); //Stornieren
				$form->addElement("<td>{$row['comment']}</td>"); //Hinweis
				$form->addElement("</tr>\n");
			}
			$form->addElement("<tr>");
			$form->addElement("<th>Gesamt</th><th></th><th></th><th>".sprintf("%.2f &euro;", $this->fetchPricesum($_SERVER['REMOTE_USER']))."</th><th></th><th></th></tr>\n");
			$form->addElement("</table>");
			if($this->getConf('active')==0||$closed) {
				$form->addElement("<input type=\"reset\" disabled=\"disabled\" value=\"".$this->getLang('btn_closed')."\" />");
			} else {
				//$form->addElement("<input type=\"submit\" value=\"".$this->getLang('btn_cancel')."\" />");
				$form->addElement(form_makeButton('submit', 'show', $this->getLang('btn_cancel')));
			}
			$form->endFieldSet();
			$renderer->doc .= $form->getForm();
			
			$res->free ();
		}
        
        return true;
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
        $sql = 'SELECT d.title, i.format, i.duplex, i.price, d.comment, i.id, d.filename, o.paymentState ';
        $sql .= 'FROM '.$this->getConf('db_prefix').'orders o ';
        $sql .= 'JOIN '.$this->getConf('db_prefix').'orderitems i ON i.order = o.id ';
        $sql .= 'JOIN '.$this->getConf('db_prefix').'documents d ON d.id = i.file ';
        $sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
        $sql .= 'WHERE u.username = ? AND i.deleted=0 ';
        $sql .= 'ORDER BY i.id';
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
		//print_r($res);
        if (PEAR::isError($res)) {
        	echo "Query3: ".htmlentities($this->res->getMessage())."<br>\n";
            return false;
        } elseif ($res->numRows()==0) {
        	//echo "notfound";
        	//msg("notfound");
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
    
    private function fetchPricesum($user) {
		//$sql='SELECT d.title, o.format, o.duplex, o.price, d.comment, o.id, d.filename, o.paid FROM '.$this->getConf('db_prefix').'orders o JOIN '.$this->getConf('db_prefix').'documents d ON d.id = o.file JOIN phpbb_users u ON u.user_id = o.user WHERE u.username=?';
        $sql = "SELECT sum(i.price) ";
    	$sql .= 'FROM skript_orderitems i ';
        $sql .= 'JOIN skript_orders o ON o.id = i.order ';
        $sql .= 'JOIN phpbb_users u ON u.user_id = o.user ';
        $sql .= 'WHERE o.semester = ? AND u.username = ? AND i.deleted=0';
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
}

// vim:ts=4:sw=4:et:
