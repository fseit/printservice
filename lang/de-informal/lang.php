<?php
/**
 * English language file for printservice plugin
 *
 * @author Florian Rinke <florian.rinke@fs-eit.de>
 */

// menu entry for admin plugins
$lang ['menu_printpay'] = 'EIT Print-Service - Ausdrucke bezahlen';
$lang ['menu_printsummary'] = 'EIT Print-Service - Bestell&uuml;bersicht';
$lang ['menu_printmapping'] = 'EIT Print-Service - Skriptzuordnung';
$lang ['menu_mail'] = 'EIT Print-Service - Prof-Mailing';
$lang ['menu_printlist'] = 'EIT Print-Service - Druckliste erzeugen';

// custom language strings for the plugin

//auth
$lang ['msg_notauthorized'] = "Du hast keinen Zugriff auf dieses Modul";

//listorders
$lang ['err_noshow'] = "Wegen eines technischen Fehlers kann deine Bestllung leider nicht angezeigt werden. Falls der Fehler in 5 Minuten immer noch auftritt, gib uns bitte Bescheid.";
$lang ['field_yourorder'] = "Deine Bestellung im ";
$lang ['tbl_doc'] = "Skript";
$lang ['tbl_format'] = "Format";
$lang ['tbl_pagemode'] = "Doppelseitig";
$lang ['tbl_price'] = "Preis";
$lang ['tbl_storno'] = "Stornieren";
$lang ['tbl_cancel'] = "Löschen";
$lang ['tbl_comment'] = "Hinweis";
$lang ['tbl_a4'] = "A4";
$lang ['tbl_a5'] = "2-auf-1";
$lang ['tbl_simplex'] = "einseitig";
$lang ['tbl_duplex'] = "doppelseitig";
$lang ['btn_closed'] = "Die Bestellung ist abgeschlossen";
$lang ['btn_cancel'] = "Markierte Skripte stornieren";
$lang ['err_exec'] = "Ein Fehler ist aufgetreten. Das h&auml;tte nicht passieren sollen... Bitte gib uns Bescheid.<br />\n";
$lang ['btn_create'] = "Neue Bestellung anlegen";

//printorder
$lang ['note_noorder'] = "Bestellungen sind nur zu Anfang der Vorlesungszeit m&ouml;glich. Entweder bist du zu fr&uuml;h dran, oder zu sp&auml;t. Falls du der Meinung bist, dass ein Fehler vorliegt und jetzt eigentlich Bestellungen abgegeben werden k&ouml;nnen m&uuml;ssten, schreib uns bitte eine Mail.";
$lang ['note_orderfinal'] = "Du hast deine Bestellung bereits abgeschickt und bezahlt. Weitere Bestellungen sind nicht mehr möglich.";
$lang ['note_notfound'] = "Bitte lege zuerst eine neue Bestellung an.";
$lang ['err_noorder'] = "Wegen eines technischen Fehlers sind derzeit leider keine Bestellungen möglich. Falls der Fehler in 5 Minuten immer noch auftritt, gib uns bitte Bescheid.";
$lang ['tbl_choosedoc'] = "1. Skripten zum Druck ausw&auml;hlen";
$lang ['tbl_semester'] = "Semester";
$lang ['tbl_lecture'] = "Fach";
$lang ['tbl_file'] = "Datei";
$lang ['tbl_pages'] = "Seiten";
$lang ['tbl_baseprice'] = "Grundpreis";
$lang ['tbl_order'] = "bestellen";
$lang ['tbl_chooseformat'] = "2. Druckfomat festlegen";
$lang ['tbl_choosepagemode'] = "3. Seitenmodus festlegen";
$lang ['tbl_sendorder'] = "4. Bestellung abschicken";
$lang ['btn_sendorder'] = "Bestellung abschicken";

//printsummary
$lang ['err_nolist'] = "Wegen eines technischen Fehlers kann die Bestellliste leider nicht angezeigt werden. Falls der Fehler in 5 Minuten immer noch auftritt, gib mir bitte Bescheid.";
$lang ['tbl_customer'] = "Besteller";
$lang ['tbl_delete'] = "Löschen";
$lang ['btn_delete'] = "Markierte Bestellungen löschen";
$lang ['btn_show'] = "Markierte Bestellung anzeigen";
$lang ['ordersum'] = "Bestellsumme";
$lang ['tbl_pay'] = "Bezahlen";
$lang ['tbl_orderid'] = "Bestellnr.";
$lang ['tbl_username'] = "Nick";
$lang ['tbl_paid'] = "Bezahlt?";
$lang ['tbl_status'] = "Status";
$lang ['unpaid'] = "Offen";
$lang ['paid'] = "Bestellt";
$lang ['printed'] = "Gedruckt";
$lang ['delivered'] = "Abgeholt";
$lang ['canceled'] = "Gelöscht";

//printpay
$lang ['msg_deleted'] = "Die markierten Eintr&auml;ge wurden gel&ouml;scht.";
$lang ['field_foundusers'] = "Gefundene Benutzer";
$lang ['tbl_realname'] = "Realname";
$lang ['btn_choose'] = "Auswählen";
$lang ['note_hasnoorder'] = "Der Benutzer hat keine Bestellung eingetragen";
$lang ['note_paid'] = "Der Benutzer hat bereits bezahlt";
$lang ['field_payment'] = "Bezahlung";
$lang ['tbl_total'] = "Gesamt";
$lang ['btn_markpaid'] = "Als bezahlt markieren";
$lang ['msg_paid'] = "Bezahlung abgeschlossen";
$lang ['msg_limit_reached'] = "Bestellung nicht möglich, das Limit ist erreicht!";

//printprocess
$lang ['msg_reallyclosed'] = "Die Bestellung ist wirklich gesschlossen!";
$lang ['msg_created'] = "Bestellung wurde angelegt";
$lang ['msg_added'] = "Skripte wurden zur Bestellung hinzugefügt";

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
$lang ['mail_recipient'] = "Empfänger";
$lang ['mail_from'] = "Absender";
$lang ['mail_subject'] = "Betreff";
$lang ['mail_user'] = "User";
$lang ['mail_pw'] = "Passwort";
$lang ['mail_host'] = "Mailserver";
$lang ['mail_port'] = "Port";
$lang ['field_mailtext'] = "Text der eMails";
$lang ['field_mailsettings'] = "Einstellungen für den Versand";
$lang ['btn_mailsend'] = "Starte Versand";
$lang ['mail_send'] = "eMails versenden";
$lang ['mail_test'] = "Test-Modus";

//printlist
$lang ['field_listtype'] = "Art der Liste";
$lang ['field_listaction'] = "Aktionen ausführen";
$lang ['btn_generatelist'] = "Liste erzeugen";
