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

class admin_plugin_printservice_mail extends DokuWiki_Admin_Plugin {
	var $output = "";
	
	function getInfo() {
		return array(
				'author' => 'Florian Rinke',
				'email'  => 'florian.rinke@fs-eit.de',
				'date'   => '2012-08-24',
				'name'   => 'Mailing',
				'desc'   => 'Sendet Kontroll-Mails an alle Dozenten',
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
		return $this->getLang ( 'menu_mail' );
	}
	public function handle() {
	$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("mail")) {
			return;
		}
		if (isset ( $_REQUEST ['mail_user'] )) {
			$semester = $this->getConf ( 'semester' );
			$dbhelper = & plugin_load ( 'helper', 'printservice_database' );
			$dbhelper->dbConnect ();
			
			$profs = $dbhelper->fetchLecturersForSemester ( $semester );
			$query_lec = $dbhelper->prepareLecturesForSemester ();
			$query_doc = $dbhelper->prepareDocumentsForSemester ();
			
			//echo "rows found: ".$res->numRows()."\n";
			//echo "\n==========================================================================\n";
			foreach ( $profs as $prof ) {
				//echo "params: ".$semester." - ".$row['lecturer']."\n";
				$lectures = $dbhelper->fetchLecturesForSemester ( $query_lec, $semester, $prof ['lecturer'] );
				$documents = $dbhelper->fetchDocumentsForSemester ( $query_doc, $semester, $prof ['lecturer'] );
				//echo inform($row['gender'], $row['name'], $lectures, $documents);
				//echo "==========================================================================\n\n\n";
				//print_r ( $row );
				$this->sendmail ( $prof ['mail'], $_REQUEST ['mail_subject'], $this->inform ( $prof ['gender'], $prof ['name'], $lectures, $documents ) );
			}
		}
	}
	
	public function html() {
		$authhelper = & plugin_load ( 'helper', 'printservice_auth' );
		if(!$authhelper->isAllowed("mail")) {
			ptln(hsc($this->getLang('msg_notauthorized')));
			return;
		}
		if($this->output != "") {
			ptln($this->output);
		} 
		
		ptln ( '<h1>' . $this->getLang ( 'menu_printsummary' ) . '</h1>' );
		ptln ( hsc ( '(Texte können in den Plugin-Einstellungen geändert werden' ) );
		$form = new Doku_Form ( array ('id' => 'mail_text', 'method' => 'POST' ) );
		$form->startFieldSet ( $this->getLang ( 'field_mailtext' ) );
		$form->addHidden ( 'page', 'printservice_mail' );
		$form->addElement ( '<textarea cols="120" rows="8" disabled="disabled" name="mail_text_1header">' . hsc ( $this->getConf ( 'mail_text_1header' ) ) . '</textarea><br />' . "\n" );
		$form->addElement ( '(Liste der Veranstaltungen)<br />' . "\n" );
		$form->addElement ( '<textarea cols="120" rows="4" disabled="disabled" name="mail_text_2qLectures">' . hsc ( $this->getConf ( 'mail_text_2qLectures' ) ) . '</textarea><br />' . "\n" );
		$form->addElement ( '(Liste der Dokumente)<br />' . "\n" );
		$form->addElement ( '<textarea cols="120" rows="3" disabled="disabled" name="mail_text_3qDocuments">' . hsc ( $this->getConf ( 'mail_text_3qDocuments' ) ) . '</textarea><br />' . "\n" );
		$form->addElement ( '<textarea cols="120" rows="5" disabled="disabled" name="mail_text_4footer">' . hsc ( $this->getConf ( 'mail_text_4footer' ) ) . '</textarea><br />' . "\n" );
		$form->endFieldSet ();
		
		$form->startFieldSet ( $this->getLang ( 'field_mailsettings' ) );
		$form->addElement ( '(Werte können direkt geändert werden, werden aber nicht dauerhaft gespeichert' . "<br />\n" );
		$form->addElement ( '<table>' . "\n" );
		$form->addElement ( '<tr><td><label for="mail_recipient">' . $this->getLang ( 'mail_recipient' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_recipient" name="mail_recipient" value="' . (isset ( $_REQUEST ['mail_recipient'] ) ? hsc ( $_REQUEST ['mail_recipient'] ) : $this->getConf ( 'mail_recipient' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_from">' . $this->getLang ( 'mail_from' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_from" name="mail_from" value="' . (isset ( $_REQUEST ['mail_from'] ) ? hsc ( $_REQUEST ['mail_from'] ) : $this->getConf ( 'mail_from' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_subject">' . $this->getLang ( 'mail_subject' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_subject" name="mail_subject" value="' . (isset ( $_REQUEST ['mail_subject'] ) ? hsc ( $_REQUEST ['mail_subject'] ) : $this->getConf ( 'mail_subject' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_user">' . $this->getLang ( 'mail_user' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_user" name="mail_user" value="' . (isset ( $_REQUEST ['mail_user'] ) ? hsc ( $_REQUEST ['mail_user'] ) : $this->getConf ( 'mail_user' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_pw">' . $this->getLang ( 'mail_pw' ) . '</label></td>' );
		$form->addElement ( '<td><input type="password" size="50" id="mail_pw" name="mail_pw" value="' . (isset ( $_REQUEST ['mail_pw'] ) ? hsc ( $_REQUEST ['mail_pw'] ) : $this->getConf ( 'mail_pw' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_host">' . $this->getLang ( 'mail_host' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_host" name="mail_host" value="' . (isset ( $_REQUEST ['mail_host'] ) ? hsc ( $_REQUEST ['mail_host'] ) : $this->getConf ( 'mail_host' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_port">' . $this->getLang ( 'mail_port' ) . '</label></td>' );
		$form->addElement ( '<td><input type="text" size="50" id="mail_port" name="mail_port" value="' . (isset ( $_REQUEST ['mail_port'] ) ? hsc ( $_REQUEST ['mail_port'] ) : $this->getConf ( 'mail_port' )) . '" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_test">' . $this->getLang ( 'mail_test' ) . '</label></td>' );
		$form->addElement ( '<td><input type="checkbox" id="mail_test" name="mail_test" checked="checked" /></td></tr>' . "\n" );
		
		$form->addElement ( '<tr><td><label for="mail_send">' . $this->getLang ( 'mail_send' ) . '</label></td>' );
		$form->addElement ( '<td><input type="checkbox" id="mail_send" name="mail_send" disabled="disabled" /></td></tr>' . "\n" );
		
		$form->addElement ( '</table>' . "\n" );
		$form->endFieldSet ();
		
		$form->addElement ( form_makeButton ( 'submit', 'admin', $this->getLang ( 'btn_mailsend' ) ) );
		$form->printForm ();
	
	}
	
	private function dbConnect() {
		$dsn = 'mysql://' . $this->getConf ( 'db_user' ) . ':' . $this->getConf ( 'db_password' ) . '@' . $this->getConf ( 'db_server' ) . '/' . $this->getConf ( 'db_database' );
		$this->mdb2 = & MDB2::connect ( $dsn );
		if (PEAR::isError ( $this->mdb2 )) {
			die ( "connect: " . $this->mdb2->getMessage () );
		}
		$this->mdb2->setFetchMode ( MDB2_FETCHMODE_ASSOC );
		$this->mdb2->setCharset ( 'utf8' );
		return true;
	}
	
	function inform($gender, $name, $lectures, $documents) {
		$text = ($gender == 'm' ? 'Sehr geehrter Herr ' : 'Sehr geehrte Frau ');
		$text .= $name . ",\n\n";
		
		//1header
		$text .= $this->getConf ( 'mail_text_1header' );
		$text .= "\n";
		foreach ( $lectures as $value ) {
			$text .= " - " . $value ['edv_id'] . " - " . $value ['name'] . "\n";
		}
		//2qLecture
		$text .= $this->getConf ( 'mail_text_2qLectures' );
		$text .= "\n";
		if (! empty ( $documents )) {
			foreach ( $documents as $value ) {
				$date = DateTime::createFromFormat ( 'Y-m-d', $value ['update'] );
				$text .= " - " . $value ['title'] . " http://skripte.fs-eit.de/protected/" . $value ['filename'] . " (" . $value ['author'] . ", Stand " . $date->format ( 'd.m.Y' ) . ")\n";
			}
			$text .= "\naus Sicherheitsgründen sind die Skripte mit einem Kennwort geschützt\n";
			$text .= "Benutzer: " . $this->getConf ( 'mail_skriptuser' ) . "\n";
			$text .= "Kennwort: " . $this->getConf ( 'mail_skriptpw' ) . "\n";
		} else {
			$text .= " - (keine)\n";
		}
		
		$text .= "\n";
		//3qDocument
		$text .= $this->getConf ( 'mail_text_3qDocuments' );
		//4qfooter
		$text .= $this->getConf ( 'mail_text_4footer' );
		return $text;
	}
	
	private function sendmail($to, $subject, $text) {
		$this->output .=  "Sende an " . $to . " ...";
		
		$recipients = $_REQUEST ['mail_recipient'];
		$headers ['Content-Type'] = "text/plain; charset=\"UTF-8\"";
		$headers ['Content-Transfer-Encoding'] = "8bit";
		$headers ['Date'] = date ( "r" );
		$headers ['From'] = $_REQUEST ['mail_from'];
		$headers ['To'] = $to; //$to
		$headers ['Cc'] = 'Fachschaft EIT <info@fs-eit.de>'; //fix
		$headers ['Reply-To'] = 'Fachschaft EIT <skriptendruck@fs-eit.de>'; //fix
		if (isset ( $_REQUEST ['mail_test'] ) && "on" == $_REQUEST ['mail_test']) {
			$headers ['Subject'] = $subject . " (" . $to . ")";
			$recipients = $_REQUEST ['mail_recipient'];
		} else {
			$headers ['Subject'] = $subject;
			$recipients = $to.', '.$_REQUEST ['mail_recipient'];
			//$recipients = $_REQUEST ['mail_recipient'];
		}
		$body = $text;
		$smtpinfo ["host"] = $_REQUEST ['mail_host'];
		$smtpinfo ["port"] = $_REQUEST ['mail_port'];
		$smtpinfo ["auth"] = true;
		$smtpinfo ["username"] = $_REQUEST ['mail_user'];
		$smtpinfo ["password"] = $_REQUEST ['mail_pw'];
		$smtpinfo ["persist"] = true;
		
		if (isset ( $_REQUEST ['mail_send'] ) && "on" == $_REQUEST ['mail_send']) {
			$mailer = & Mail::factory ( "smtp", $smtpinfo );
			$send = $mailer->send ( $recipients, $headers, $body );
			if (PEAR::isError ( $send )) {
				$this->output .= "  " . $send->getMessage () . "<br />\n";
			} else {
				$this->output .= " done.<br />\n";
			}
		} else {
			$this->output .= " simulate.<br />\n";
		}
	}
	
}
