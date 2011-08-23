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

class admin_plugin_printservice_printsummary extends DokuWiki_Admin_Plugin {

    public function getMenuSort() { return FIXME; }
    public function forAdminOnly() { return false; }
    public function getMenuText() { return $this->getLang('menu_printsummary'); }
    public function handle() {
    	$this->dbConnect ();
    	//print_r($_REQUEST['stornoId']);
    	if(is_array($_REQUEST['stornoId'])) {
    		msg($this->getLang('msg_deleted'));
    	} else return;
    	
    	if(!checkSecurityToken()){
    		msg("nanana!");
    		return;
    	} 
    	//$delete = array();
    	foreach ($_REQUEST['stornoId'] as $value) {
    		if(is_numeric($value)) $delete[]=(int)$value;
  		}
    	$this->deleteOrders($delete);
    }
	
	public function html() {
		ptln ( '<h1>' . $this->getLang ( 'menu_printsummary' ) . '</h1>' );
		
		$this->dbConnect ();
		if (! $res = $this->fetchAllOrders()) {
			ptln("<p><div class=\"notewarning\">".$this->getLang('err_nolist')."</div></p>");
		} else {
			$form = new Doku_Form ( array ('id' => 'delorder', 'method' => 'POST' ) );
        	$form->addHidden('page','printservice_printsummary');
			$form->addElement ( "<table>\n<tr>" );
			$form->addElement ( "<th>".$this->getLang('tbl_customer')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_doc')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_format')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_pageformat')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_pages')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_price')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_delete')."</th>" );
			$form->addElement ( "<th>".$this->getLang('tbl_comment')."</th>" );
			$form->addElement ( "</tr>" );
			while ( $row = $res->fetchRow () ) {
				//f.title, o.format, o.duplex, d.pages o.preis, f.comment, o.id, d.filename, o.bezahlt
				$form->addElement ( "<tr>" );
				$form->addElement ( "<td>{$row['realname']}</td>" ); //Skript
				$form->addElement ( "<td><a href=\"{$row['filename']}\">{$row['title']}</a></td>" ); //Skript
				$form->addElement ( "<td>" . ($row ['format'] == "a4" ? $this->getLang('tbl_a4') : $this->getLang('tbl_a5')) . "</td>" ); //Format
				$form->addElement ( "<td>" . ($row ['duplex'] == "simplex" ? $this->getLang('tbl_simplex') : $this->getLang('tbl_duplex')) . "</td>" ); //Doppelseitig
				$form->addElement ( "<td>{$row['pages']}</td>" ); //Preis
				$form->addElement ( "<td>" . sprintf ( "%.2f &euro;", $row ['price'] ) . "</td>" ); //Preis
				$form->addElement ( "<td><input type=\"checkbox\" name=\"stornoId[]\" value=\"{$row['id']}\" /></td>" ); //Stornieren
				$form->addElement ( "<td>{$row['comment']}</td>" ); //Hinweis
				$form->addElement ( "</tr>\n" );
			}
			$form->addElement ( "</table>" );
			$form->addElement(form_makeButton('submit', 'admin', $this->getLang('btn_delete')));
			$form->printForm();
			
			$res->free ();
			
			$res=$this->fetchOrderStats();
			ptln("<table>");
			ptln("<tr><th>".$this->getLang('tbl_pages')."</th><td>{$res['pagesum']}</td></tr>");
			ptln("<tr><th>".$this->getLang('ordersum')."</th><td>".sprintf("%.2f &euro;",$res['pricesum'])."</td></tr>");
			ptln("<table>");
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
	
    private function fetchOrderStats() {
        $this->mdb2->loadModule('Extended', null, false);
    	$sql="SELECT SUM(d.pages) as pagesum, SUM(o.price) as pricesum FROM ".$this->getConf('db_prefix')."orders o JOIN ".$this->getConf('db_prefix')."documents d ON d.id = o.file WHERE o.semester=?";
        //echo "sql3: ". htmlentities($sql)."<br />\n";
        //$row = $this->mdb2->queryRow ($sql);
        $row = $this->mdb2->extended->getRow($sql, null, array($this->getConf('semester')), array('text'));
        if (PEAR::isError($row)) {
            die("Query1: ".$row->getMessage());
        }
        return $row;
    }
    
    private function fetchAllOrders() {
		$sql='SELECT p.pf_realname as realname, d.title, o.format, o.duplex, d.pages, o.price, d.comment, o.id, d.filename FROM '.$this->getConf('db_prefix').'orders o JOIN '.$this->getConf('db_prefix').'documents d ON d.id = o.file JOIN phpbb_profile_fields_data p ON p.user_id = o.user WHERE o.semester=?';
        $sqldata=$this->getConf('semester');
        $sqltype=array('text');
        //echo "sql2: ". htmlentities($sql)."<br />\n";
        //echo "sqldata2: ". htmlentities($sqldata)."<br />\n";
        $query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
    	if (PEAR::isError($query)) {
            echo $query->getMessage();
            return false;
        }
		$res = $query->execute($sqldata);
        if (PEAR::isError($res)) {
        	echo "Query2: ".htmlentities($this->res->getMessage())."<br>\n";
            return false;
        } elseif ($res == DB_OK or empty($res)) {
            echo $this->getLang('err_exec');
            return false;
        }
        return $res;
    }

	private function deleteOrders($ids) {
        $this->mdb2->loadModule('Extended', null, false);
		$sql="DELETE FROM `".$this->getConf('db_prefix')."orders` WHERE id=?";
        $sqltype=array('integer');
        $query = $this->mdb2->prepare($sql,$sqltype,MDB2_PREPARE_RESULT);
        $this->mdb2->extended->executeMultiple($query,$ids);

        if (PEAR::isError($res)) {
            die("Query1: ".$res->getMessage());
        }
        return $row;
    }
}

// vim:ts=4:sw=4:et:
