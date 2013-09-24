<?php
/**
 * Options for the printservice plugin
 *
 * @author Florian Rinke <florian.rinke@fs-eit.de>
 */

//ACL
$meta ['_acl'] = array ('fieldset' );
$meta ["user_printmapping"] = array ('string' );
$meta ["user_mail"] = array ('string' );
$meta ["user_printsummary"] = array ('string' );
$meta ["user_printpay"] = array ('string' );
$meta ["user_printlist"] = array ('string' );
$meta ["user_printcover"] = array ('string' );

//database
$meta ['_database'] = array ('fieldset' );
$meta ['db_server'] = array ('string' );
$meta ['db_user'] = array ('string' );
$meta ['db_password'] = array ('password' );
$meta ['db_database'] = array ('string' );
$meta ['db_prefix'] = array ('string' );
$meta ['semester'] = array ('multichoice', '_choices' => array ('11ws', '12ss', '12ws', '13ss', '13ws', '14ss', '14ws', '15ss', '15ws', '16ss' ) );
$meta ['active'] = array ('onoff' );
$meta ['pagecost'] = array ('string' );
$meta ['dlpath'] = array ('string' );

//mail
$meta ['_mail'] = array ('fieldset' );
$meta ['mail_text_1header'] = array('string');
$meta ['mail_text_2qLectures'] = array('string');
$meta ['mail_text_3qDocuments'] = array('string');
$meta ['mail_text_4footer'] = array('string');
$meta ['mail_recipient'] = array ('string' );
$meta ['mail_from'] = array ('string' );
$meta ['mail_subject'] = array ('string' );
$meta ["mail_user"] = array ('string' );
$meta ["mail_pw"] = array ('password' );
$meta ["mail_host"] = array ('string' );
$meta ["mail_port"] = array ('numeric' );
$meta ["mail_skriptuser"] = array ('string' );
$meta ["mail_skriptpw"] = array ('string' );
