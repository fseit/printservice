<?php
/**
 * english language file for printservice plugin
 *
 * @author Florian Rinke <florian.rinke@fs-eit.de>
 */

// keys need to match the config setting name
// $lang['fixme'] = 'FIXME';

$lang['_general'] = "General Settings";
$lang['_acl'] = "Access Rights";
$lang['_database'] = "Database";
$lang['_mail'] = "Mailing";

$lang['semester'] = 'The current semester, for which orders will be processed';
$lang['active']   = 'can orders be placed or edited?';
$lang['pagecost']   = 'Printing cost per page in cent';
$lang['dlpath']   = 'URL-Prefix, where the documents can be accessed';

$lang['user_printmapping'] = "These users are allowed to edit mappings";
$lang['user_mail'] = "These users are allowed to send mass-mailings to staff";
$lang['user_printsummary'] = "These users are allowed to view the list of orders";
$lang['user_printpay'] = "These users are allowed to process payments";
$lang['user_printcover'] = "These users are allowed to generate covers";
$lang['user_printlist'] = "These users are allowed to generate lists for printing";
$lang['user_ignorelimit'] = "These users can still enter payments after the limit was reached";

$lang['db_server']   = 'address of mysql-servers';
$lang['db_user']     = 'username used for mysql-server';
$lang['db_password'] = 'password for mysql-server';
$lang['db_database'] = 'database used on mysql-server';
$lang['db_prefix']   = 'table prefix used in table';

$lang ['mail_text_1header'] = "Header for eMail incl. opening";
$lang ['mail_text_2qLectures'] = "Text between lists of lectures and documents";
$lang ['mail_text_3qDocuments'] = "Text after list of documents";
$lang ['mail_text_4footer'] = "Footer for Mail incl. greeting;";
$lang ['mail_recipient'] = "all eMails are additionally (exclusively in test-mode) sent to this/these address(es)";
$lang ['mail_from'] = "sender of eMail";
$lang ['mail_subject'] = "Subject of eMail";
$lang ["mail_user"] = "Username for mailserver";
$lang ["mail_pw"] = "Password for mailserver";
$lang ["mail_host"] = "Hostname of mailservers";
$lang ["mail_port"] = "Port of mailservers";
$lang ["mail_skriptuser"] = "username for viewing documents viw www";
$lang ["mail_skriptpw"] = "associated password for viewing";
