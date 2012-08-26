<?php
/**
 * English language file for printservice plugin
 *
 * @author Florian Rinke <florian.rinke@fs-eit.de>
 */

// menu entry for admin plugins
$lang ['menu_printpay'] = 'EIT Print-Service - Pay Orders';
$lang ['menu_printsummary'] = 'EIT Print-Service - Order Overview';
$lang ['menu_printmapping'] = 'EIT Print-Service - Skript mapping';
$lang ['menu_mail'] = 'EIT Print-Service - Prof-Mailing';
$lang ['menu_printlist'] = 'EIT Print-Service - Create list of pending prints';

// custom language strings for the plugin

//auth
$lang ['msg_notauthorized'] = "You are not authorized to access this module";

//listorders
$lang ['err_noshow'] = "Your order can't be shown due to a technical malfunction. If this error still occurs in 5 minutes, please notify us by email.";
$lang ['field_yourorder'] = "Your order in ";
$lang ['tbl_doc'] = "Lecture note";
$lang ['tbl_format'] = "format";
$lang ['tbl_pagemode'] = "pagemode";
$lang ['tbl_price'] = "price";
$lang ['tbl_storno'] = "cancel";
$lang ['tbl_cancel'] = "delete";
$lang ['tbl_comment'] = "note";
$lang ['tbl_a4'] = "A4";
$lang ['tbl_a5'] = "2-on-1";
$lang ['tbl_simplex'] = "single-sided";
$lang ['tbl_duplex'] = "dual-sided";
$lang ['btn_closed'] = "Orders are closed";
$lang ['btn_cancel'] = "Cancel marked orders";
$lang ['err_exec'] = "An error has occured. That should not have happened... Please notify us by email.<br />\n";
$lang ['btn_create'] = "Create new order";

//printorder
$lang ['note_noorder'] = "Orders are possible at the beginning of a semester only. Either you're too early or too late. If you think there is an error, and it should be possible to submit orders right now, please tell us by email.";
$lang ['note_orderfinal'] = "You already submitted and paid for your order. Additional orders are not allowed.";
$lang ['note_notfound'] = "Please create a new order first.";
$lang ['err_noorder'] = "Due to a technical malfunction no orders can be submitted at the moment. If this error still occurs in 5 minutes, please notify us by email.";
$lang ['tbl_choosedoc'] = "1. Choose documents for print";
$lang ['tbl_semester'] = "semester";
$lang ['tbl_lecture'] = "subject";
$lang ['tbl_file'] = "file";
$lang ['tbl_pages'] = "pages";
$lang ['tbl_baseprice'] = "base price";
$lang ['tbl_order'] = "order";
$lang ['tbl_chooseformat'] = "2. select print formatn";
$lang ['tbl_choosepagemode'] = "3. select pagemode";
$lang ['tbl_sendorder'] = "4. submit order";
$lang ['btn_sendorder'] = "submit order";

//printsummary
$lang ['err_nolist'] = "The orders can't be shown due to a technical malfunction. If this error still occurs in 5 minutes, please notify us by email.";
$lang ['tbl_customer'] = "Customer";
$lang ['tbl_delete'] = "delete";
$lang ['btn_delete'] = "delete selected orders";
$lang ['btn_show'] = "show selected orders";
$lang ['ordersum'] = "order sum";
$lang ['tbl_pay'] = "pay";
$lang ['tbl_orderid'] = "Order no.";
$lang ['tbl_username'] = "nick";
$lang ['tbl_paid'] = "paid?";
$lang ['tbl_status'] = "status";
$lang ['unpaid'] = "open";
$lang ['paid'] = "ordered";
$lang ['printed'] = "printed";
$lang ['delivered'] = "delivered";
$lang ['canceled'] = "canceled";

//printpay
$lang ['msg_deleted'] = "Marked entries were deleted";
$lang ['field_foundusers'] = "Found users";
$lang ['tbl_realname'] = "real name";
$lang ['btn_choose'] = "choose";
$lang ['note_hasnoorder'] = "This user has not submitted any order";
$lang ['note_paid'] = "This user has paid already";
$lang ['field_payment'] = "payment";
$lang ['tbl_total'] = "total";
$lang ['btn_markpaid'] = "mark paid";
$lang ['msg_paid'] = "Payment complete";

//printprocess
$lang ['msg_reallyclosed'] = "Ordering is really closed!";
$lang ['msg_created'] = "created order";
$lang ['msg_added'] = "Added documents to order";

//printmapping
$lang ['err_dbdown'] = "Die Datenbank ist derzeit nicht verfügbar. Du solltest selbst wissen was jetzt zu tun ist, schließlich bist du der Admin ;-)";
$lang ['field_newmapping'] = "Zuordnungen neu anlegen";
$lang ['btn_savemapping'] = "Zuordnung speichern";
$lang ['field_loadsemester'] = "Zuordnungen aus altem Semester laden";
$lang ['btn_loadsemester'] = "Semester laden";
$lang ['field_editmappings'] = "Zuordnungen bearbeiten";
$lang ['field_editunknownmappings'] = "TODO";
$lang ['tbl_lecturer'] = "Dozent";
$lang ['tbl_id'] = "Id";

//mail
$lang ['mail_recipient'] = "Recipient";
$lang ['mail_from'] = "Sender";
$lang ['mail_subject'] = "Subject";
$lang ['mail_user'] = "User";
$lang ['mail_pw'] = "Password";
$lang ['mail_host'] = "Mailserver";
$lang ['mail_port'] = "Port";
$lang ['field_mailtext'] = "Text of the eMails";
$lang ['field_mailsettings'] = "Settings for Sending";
$lang ['btn_mailsend'] = "Start sending";
$lang ['mail_send'] = "send eMails";
$lang ['mail_test'] = "Test-Mode";
