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
if (!defined('DOKU_PLUGIN'))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'admin.php';
require_once ('MDB2.php');

class admin_plugin_printservice_printmapping extends DokuWiki_Admin_Plugin {
	
	public function getMenuSort() {
		return FIXME;
	}
	public function forAdminOnly() {
		return true;
	}
	public function getMenuText() {
		return $this->getLang ( 'menu_printmapping' );
	}
	public function handle() {
		$this->dbConnect ();
		//echo "request: <pre>".print_r($_REQUEST,true)."</pre><br />\n";
		//insert
		$data = array ();
		if (isset ( $_REQUEST ['edit_id'] )) {
			if (isset ( $_REQUEST ['oldsemester'] )) {
				//save as new entries for current semester
				foreach ( $_REQUEST ['edit_id'] as $key => $value ) {
					$data [] = array ('semester' => $this->getConf ( 'semester' ), 
										'lecture' => (( int ) $_REQUEST ['edit_lecture'][$key]), 
										'lecturer' => (( int ) $_REQUEST ['edit_lecturer'][$key]), 
										'document' => (( int ) $_REQUEST ['edit_document'][$key]) );
				}
				$this->addMappings ( $data );
			
			} else {
				//edit existing entries
				foreach ( $_REQUEST ['edit_id'] as $key => $value ) {
					if (! in_array ( $value, $_REQUEST ['edit_delete'] )) {
						$data [] = array ('lecturer' => (( int ) $_REQUEST ['edit_lecturer'][$key]), 
											'document' => (( int ) $_REQUEST ['edit_document'][$key]), 
											'id' => (( int ) $value));
					}
				}
				$this->editMappings ( $data );
			}
		} elseif (isset ( $_REQUEST ['add_lecture'] )) {
			//add new entries
			foreach ( $_REQUEST ['add_lecture'] as $key => $value ) {
				if ($value != 0) {
				$data [] = array ('semester' => $this->getConf ( 'semester' ), 
									'lecture' => (( int ) $_REQUEST ['add_lecture'][$key]), 
									'lecturer' => (( int ) $_REQUEST ['add_lecturer'][$key]), 
									'document' => (( int ) $_REQUEST ['add_document'][$key]) );
				}
			}
			$this->addMappings ( $data );
		}
		
		//delete
		if (isset ( $_REQUEST ['edit_delete'] )) {
			$this->deleteMappings ( $_REQUEST ['edit_delete'] );
		}
	
	}
	
	public function html() {
		ptln ( '<h1>' . $this->getLang ( 'menu_printmapping' ) . '</h1>' );
		$this->dbConnect ();
		if (! $semesters = $this->fetchAllSemesters ()) {
			ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'err_dbdown' ) . "</div></p>" );
		} else {
			//fetch data
			$lectures = $this->fetchLectures ();
			$lecturers = $this->fetchLecturers ();
			$documents = $this->fetchDocuments ();
			$semester = isset ( $_REQUEST ['load_semester'] ) && array_key_exists ( $_REQUEST ['load_semester'], $semesters ) ? $_REQUEST ['load_semester'] : $this->getConf ( 'semester' );
			$mapping = $this->fetchMappings ( $semester );
			
			//insert
			$form = new Doku_Form ( array ('id' => 'insertmapping', 'method' => 'POST' ) );
			$form->startFieldSet ( $this->getLang ('field_newmapping') );
			$form->addHidden ( 'page', 'printservice_printmapping' );
			$form->addElement ( '<table><tr><th>'.$this->getLang ('tbl_lecture').'</th><th>'.$this->getLang ('tbl_lecturer').'</th><th>'.$this->getLang ('tbl_doc').'</th></tr>' );
			for($i = 0; $i < 5; $i ++) {
				$form->addElement ( '<tr><td><select size="1" name="add_lecture[]">' );
				$form->addElement ( '<option value="0">---</option>' );
				foreach ( $lectures as $key => $value ) {
					$form->addElement ( '<option value="' . $key . '">' . $value . '</option>' );
				}
				$form->addElement ( '</select></td><td><select size="1" name="add_lecturer[]">' );
				$form->addElement ( '<option value="0">---</option>' );
				foreach ( $lecturers as $key => $value ) {
					$form->addElement ( '<option value="' . $key . '">' . $value . '</option>' );
				}
				$form->addElement ( '</select></td><td><select size="1" name="add_document[]">' );
				$form->addElement ( '<option value="0">---</option>' );
				foreach ( $documents as $key => $value ) {
					$form->addElement ( '<option value="' . $key . '">' . $value . '</option>' );
				}
				$form->addElement ( '</select></td></tr>' );
			}
			$form->addElement ( '</table>' );
			$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ('btn_savemapping') ) );
			$form->endFieldSet ();
			$form->printForm ();
			if ($mapping->numRows () == 0) {
				//load
				$form = new Doku_Form ( array ('id' => 'loadsemester', 'method' => 'POST' ) );
				$form->startFieldSet ( $this->getLang ('field_loadsemester') );
				$form->addHidden ( 'page', 'printservice_printmapping' );
				$form->addElement ( '<select size="1" name="load_semester">' );
				foreach ( $semesters as $key => $value ) {
					$form->addElement ( '<option value="' . $key . '"' . ($key == $semester ? ' selected="selected"' : '') . '>' . $value . '</option>' );
				}
				$form->addElement ( '</select>' );
				$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ('btn_loadsemester') ) );
				$form->endFieldSet ();
				$form->printForm ();
			} else {
				//edit
				$form = new Doku_Form ( array ('id' => 'editmapping', 'method' => 'POST' ) );
				$form->startFieldSet ( $this->getLang ('field_editmappings') );
				$form->addHidden ( 'page', 'printservice_printmapping' );
				if ($semester != $this->getConf ( 'semester' )) {
					$form->addHidden ( 'oldsemester', 'true' );
				}
				$form->addElement ( '<table><tr><th>'.$this->getLang ('tbl_id').'</th><th>'.$this->getLang ('tbl_lecture').'</th><th>'.$this->getLang ('tbl_lecturer').'</th><th>'.$this->getLang ('tbl_doc').'</th><th>'.$this->getLang ('tbl_delete').'</th></tr>' );
				while ( $row = $mapping->fetchRow () ) {
					$form->addElement ( '<tr><td>' . $row ['id'] );
					$form->addElement ( '<input type="hidden" value="' . $row ['id'] . '" name="edit_id[]">' );
					$form->addElement ( '</td><td>' );
					$form->addElement ( hsc ( $lectures [$row ['id']] ) );
					$form->addElement ( '</td><td><select size="1" name="edit_lecturer[]">' );
					$form->addElement ( '<option value="0">---</option>' );
					foreach ( $lecturers as $key => $value ) {
						$form->addElement ( '<option value="' . $key . '"' . ($key == $row ['lecturer'] ? ' selected="selected"' : '') . '>' . $value . '</option>' );
					}
					$form->addElement ( '</select></td><td><select size="1" name="edit_document[]">' );
					$form->addElement ( '<option value="0">---</option>' );
					foreach ( $documents as $key => $value ) {
						$form->addElement ( '<option value="' . $key . '"' . ($key == $row ['document'] ? ' selected="selected"' : '') . '>' . $value . '</option>' );
					}
					$form->addElement ( '</select></td><td>' );
					$form->addElement ( '<input type="checkbox" value="' . $row ['id'] . '" name="edit_delete[]">' );
					$form->addElement ( '</td></tr>' );
				}
				$form->addElement ( '</table>' );
				$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ('btn_savemapping') ) );
				$form->endFieldSet ();
				$form->printForm ();
			}
		}
	}
	
	private function dbConnect() {
		$dsn = 'mysqli://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage () );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		return true;
	}
	
	private function fetchSemester($semester) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT name FROM " . $this->getConf ( 'db_prefix' ) . "semesters WHERE code=?";
		$row = $this->mdb2->extended->getOne ( $sql, null, array ($semester ), array ('text' ) );
		if (PEAR::isError ( $res )) {
			die ( "Query1a: " . $res->getMessage () );
		}
		return $row;
	}
	private function fetchAllSemesters() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = "SELECT code, name FROM " . $this->getConf ( 'db_prefix' ) . "semesters";
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query1b: " . $rows->getMessage () );
		}
		return $rows;
	}
	
	private function fetchOrderStats() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT SUM(d.pages) as pagesum, SUM(i.price) as pricesum FROM ' . $this->getConf ( 'db_prefix' ) . 'orderitems i ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'orders o ON o.id = i.order ';
		$sql .= 'JOIN ' . $this->getConf ( 'db_prefix' ) . 'documents d ON d.id = i.file ';
		$sql .= 'WHERE o.semester=? AND i.deleted=0 ';
		$row = $this->mdb2->extended->getRow ( $sql, null, array ($this->getConf ( 'semester' ) ), array ('text' ) );
		if (PEAR::isError ( $row )) {
			die ( "Query2a: " . $row->getMessage () );
		}
		return $row;
	}
	
	private function fetchAllOrders() {
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
			echo "Prepare2b: " . htmlentities ( $query->getMessage () );
			return false;
		}
		$res = $query->execute ( $sqldata );
		if (PEAR::isError ( $res )) {
			echo "Exec2b: " . htmlentities ( $this->res->getMessage () ) . "<br>\n";
			return false;
		} elseif ($res == DB_OK or empty ( $res )) {
			echo $this->getLang ( 'err_exec' );
			return false;
		}
		return $res;
	}
	
	private function fetchLecturers() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, name  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'lecturers ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3a: " . $rows->getMessage () );
		}
		return $rows;
	}
	
	private function fetchLectures() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, brief  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'lectures ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3b: " . $rows->getMessage () );
		}
		return $rows;
	}
	
	private function fetchDocuments() {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'SELECT id, filename  ';
		$sql .= 'FROM ' . $this->getConf ( 'db_prefix' ) . 'documents ';
		$rows = $this->mdb2->extended->getAssoc ( $sql );
		if (PEAR::isError ( $rows )) {
			die ( "Query3c: " . $rows->getMessage () );
		}
		return $rows;
	}
	
	private function fetchMappings($semester) {
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
	
	private function addMappings($mapping) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'INSERT INTO `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= '(`semester`, `lecture`, `lecturer`, `document`) ';
		$sql .= 'VALUES (:semester, :lecture , :lecturer, :document) ';
		$sqltype = array ('text', 'integer', 'integer', 'integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare4b: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $mapping );
		if (PEAR::isError ( $res )) {
			die ( "Exec4b: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
	
	private function editMappings($mapping) {
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'UPDATE `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= 'SET `lecturer`=:lecturer,`document`=:document ';
		$sql .= 'WHERE `id`=:id ';
		$sqltype = array ('integer', 'integer', 'integer' );
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
	
	private function deleteMappings($ids) {
		
		$this->mdb2->loadModule ( 'Extended', null, false );
		$sql = 'DELETE FROM `' . $this->getConf ( 'db_prefix' ) . 'mappings` ';
		$sql .= 'WHERE `id`=? ';
		$sqltype = array ('integer' );
		$query = $this->mdb2->prepare ( $sql, $sqltype );
		if (PEAR::isError ( $query )) {
			echo "Prepare4d: " . $query->getMessage ();
			return false;
		}
		$res = $this->mdb2->extended->executeMultiple ( $query, $ids );
		if (PEAR::isError ( $res )) {
			die ( "Exec4d: " . $res->getMessage () );
		}
		$query->free ();
		return true;
	}
}

// vim:ts=4:sw=4:et:
