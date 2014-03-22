<?php
/**
 * Default settings for the printservice plugin
 *
 * @author Florian Rinke <florian.rinke@fs-eit.de>
 */

//Database
$conf ['db_server'] = 'localhost';
$conf ['db_user'] = '';
$conf ['db_password'] = '';
$conf ['db_database'] = '';
$conf ['db_prefix'] = 'skript_';
$conf ['semester'] = '';
$conf ['active'] = '0';
$conf ['pagecost'] = '2.5';
$conf ['dlpath'] = '';
$conf ['pagelimit'] = '50000';

//ACL
$conf ['user_printmapping'] = 'EIT-Admin';
$conf ['user_lecturematerials'] = 'EIT-Admin';
$conf ['user_mail'] = 'EIT-Admin';
$conf ['user_printsummary'] = 'EIT-Admin';
$conf ['user_printpay'] = 'EIT-Admin';
$conf ['user_printlist'] = 'EIT-Admin';
$conf ['user_printcover'] = 'EIT-Admin';
$conf ['user_ignorelimit'] = 'EIT-Admin';

//mail
$conf ['mail_subject'] = "Skripte im [Semester]";
$conf ['mail_host'] = 'mail.fs-eit.de';
$conf ['mail_port'] = 25;
$conf ['mail_text_1header'] = "die Fachschaft EIT wird auch im kommenden Semester wieder einen Skripten-Sammeldruck für Studenten des Fachbereichs organisieren.";
$conf ['mail_text_1header'] .= "Da bei großen Druckaufträgen die Kosten pro Seite erheblich sinken, werden wir Bestellungen sammeln, gesammelt ausdrucken und die Ausdrucke zum Selbstkostenpreis an die Studierenden weitergeben.\n\n";
$conf ['mail_text_1header'] .= "Um in allen Fäern die aktuellen Unterlagen zu haben, möen wir Sie auch diesmal um Ihre Mithilfe bitten.\n\n";
$conf ['mail_text_1header'] .= "Laut der Angaben im Vorlesungsverzeichnis (LSF) werden Sie im SS13 folgende Vorlesungen/Labore halten:";
$conf ['mail_text_2qLectures'] = "(Ist das so korrekt und vollständig?)";
$conf ['mail_text_3qDocuments'] = "Werden diese Unterlagen (noch) so von Ihnen verwendet?\n";
$conf ['mail_text_3qDocuments'] .= "Haben Sie die Unterlagen aktualisiert bzw. planen Sie eine Aktualisierung für das kommende Semester?\n";
$conf ['mail_text_3qDocuments'] .= "Gibt es weitere Unterlagen, von deren Sammel-Ausdruck Studierende profitieren würden?";
$conf ['mail_text_4footer'] = "Wir bedanken uns im Voraus für Ihre Unterstützung\n\n";
$conf ['mail_text_4footer'] .= "mit freundlichen Grüßen\n";
$conf ['mail_text_4footer'] .= "[Name]\n";
$conf ['mail_text_4footer'] .= "im Auftrag der Fachschaft EIT";
$conf ['mail_recipient'] = "skriptendruck@fs-eit.de";
$conf ['mail_from'] = "[Name] (FS EIT) <[adresse]@fs-eit.de>";
$conf ['mail_subject'] = "Skripte im [Semester]";
$conf ["mail_user"] = '';
$conf ["mail_pw"] = '';
$conf ["mail_host"] = "mail.fs-eit.de";
$conf ["mail_port"] = "25";
$conf ["mail_skriptuser"] = "";
$conf ["mail_skriptpw"] = "";
