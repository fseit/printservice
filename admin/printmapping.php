<?php
/**
 * DokuWiki Plugin printservice (Admin Component)
 *
 * @author  Florian Rinke <florian.rinke@fs-eit.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
	define ( 'DOKU_PLUGIN', DOKU_INC . 'lib/plugins/' );

require_once DOKU_PLUGIN . 'admin.php';

class admin_plugin_printservice_printmapping extends DokuWiki_Admin_Plugin {
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2011-07-31',
				'name'   => 'Zuordnung',
				'desc'   => 'Verwaltet Zuordnungen Dozent-Fach-Skript',
				'url'    => 'http://www.fs-eit.de',
		);
	}
	
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
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
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
					if (! isset($_REQUEST ['edit_delete']) || ! in_array ( $value, $_REQUEST ['edit_delete'] )) {
						$data [] = array ('lecturer' => (( int ) $_REQUEST ['edit_lecturer'][$key]), 
											'document' => (( int ) $_REQUEST ['edit_document'][$key]), 
											'id' => (( int ) $value));
					}
				}
				$dbhelper->editMappings ( $data );
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
			$dbhelper->addMappings ( $data );
		}
		
		//delete
		if (isset ( $_REQUEST ['edit_delete'] )) {
			$dbhelper->deleteMappings ( $_REQUEST ['edit_delete'] );
		}
	
	}
	
	public function html() {
		ptln ( '<h1>' . $this->getLang ( 'menu_printmapping' ) . '</h1>' );
		$dbhelper =& plugin_load('helper','printservice_database');
		$dbhelper->dbConnect ();
		if (! $semesters = $dbhelper->fetchAllSemesters ()) {
			ptln ( "<p><div class=\"notewarning\">" . $this->getLang ( 'err_dbdown' ) . "</div></p>" );
		} else {
			//fetch data
			$lectures = $dbhelper->fetchLectures ();
			$lecturers = $dbhelper->fetchLecturers ();
			$documents = $dbhelper->fetchDocuments ();
			$semester = isset ( $_REQUEST ['load_semester'] ) && array_key_exists ( $_REQUEST ['load_semester'], $semesters ) ? $_REQUEST ['load_semester'] : $this->getConf ( 'semester' );
			$mapping = $dbhelper->fetchMappings ( $semester );
			$unknown = $dbhelper->fetchUnknownMappings();
			//insert
			$form = new Doku_Form ( array ('id' => 'insertmapping', 'method' => 'POST' ) );
			$form->startFieldSet ( $this->getLang ('field_newmapping') );
			$form->addHidden ( 'page', 'printservice_printmapping' );
			$form->addElement ( '<table><tr><th>'.$this->getLang ('tbl_lecture').'</th><th>'.$this->getLang ('tbl_lecturer').'</th><th>'.$this->getLang ('tbl_doc').'</th></tr>' );
			for($i = 0; $i < 3; $i ++) {
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
				//editunknown
				$form = new Doku_Form ( array ('id' => 'editunknownmapping', 'method' => 'POST' ) );
				$form->startFieldSet ( $this->getLang ('field_editunknownmappings') );
				$form->addHidden ( 'page', 'printservice_printmapping' );
				if ($semester != $this->getConf ( 'semester' )) {
					$form->addHidden ( 'oldsemester', 'true' );
				}
				$form->addElement ( '<table><tr><th>'.$this->getLang ('tbl_id').'</th><th>'.$this->getLang ('tbl_lecture').'</th><th>'.$this->getLang ('tbl_lecturer').'</th><th>'.$this->getLang ('tbl_doc').'</th><th>'.$this->getLang ('tbl_delete').'</th></tr>' );
				while ( $row = $unknown->fetchRow () ) {
					$form->addElement ( '<tr><td>' . $row ['id'] );
					$form->addElement ( '<input type="hidden" value="' . $row ['id'] . '" name="edit_id[]">' );
					$form->addElement ( '</td><td>' );
					$form->addElement ( hsc ( $lectures [$row ['lecture']] ) );
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
				$unknown->free;
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
					$form->addElement ( hsc ( $lectures [$row ['lecture']] ) );
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
				$mapping->free;
			}
		}
	}
	
}
