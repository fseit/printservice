-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 26. Aug 2011 um 19:36
-- Server Version: 5.1.56
-- PHP-Version: 5.3.6-pl0-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `usr_web893_1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_documents`
--

CREATE TABLE IF NOT EXISTS `skript_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `title` varchar(100) CHARACTER SET utf8 NOT NULL,
  `author` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `pages` int(10) unsigned DEFAULT NULL,
  `update` date NOT NULL,
  `comment` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `skript_documents`
--

INSERT INTO `skript_documents` (`id`, `filename`, `title`, `author`, `pages`, `update`, `comment`) VALUES
(1, 'nb121_get1_09ws', 'Get1 Litzenburger 09', 'Litzenburger, Strohrmann', 120, '2009-09-30', NULL),
(2, 'nb121_get1_11ws', 'Get1 Litzenburger 11', 'Litzenburger, Strohrmann', 210, '2009-09-30', 'dummy');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_lecturers`
--

CREATE TABLE IF NOT EXISTS `skript_lecturers` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `forename` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `title` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `mail` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `send_mail` tinyint(1) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `phone` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=36 ;

--
-- Daten für Tabelle `skript_lecturers`
--

INSERT INTO `skript_lecturers` (`id`, `name`, `forename`, `title`, `mail`, `send_mail`, `url`, `phone`) VALUES
(1, 'Adam ', 'Henning', 'Dipl.-Ing.', '1', 1, NULL, NULL),
(2, 'Bieber ', 'Jürgen', NULL, '1', 1, NULL, NULL),
(3, 'Brandenburger ', 'E', 'Dipl.-Ing.(FH)', '1', 1, NULL, NULL),
(4, 'Brunner ', 'Urban', 'Prof. Dr.-Ing.', '1', 1, NULL, '1512'),
(5, 'Fehrenbach ', 'Hermann', 'Prof. Dr.-Ing.', '1', 1, NULL, '2227'),
(6, 'Gentner ', 'Jürgen', 'Prof. Dr.-Ing.', '1', 1, NULL, '1475'),
(7, 'Grünhaupt ', 'Ulrich', 'Prof. Dr.-Ing.', '1', 1, NULL, '1332'),
(8, 'Halter ', 'Eberhard', 'Prof. Dr.', '1', 1, NULL, '1703'),
(9, 'Heitzmann ', 'Wilhelm', 'Dipl.-Ing.', '1', 1, NULL, NULL),
(10, 'Hoffmann ', 'Josef', 'Prof. Dr.-Ing.', '1', 1, NULL, NULL),
(11, 'Höpfel ', 'Dieter', 'Prof. Dr.', '1', 1, NULL, '1284'),
(12, 'Ihle ', 'Marc', 'Prof. Dr.-Ing.', '1', 1, NULL, NULL),
(13, 'Karnutsch ', 'Christian', 'Prof. Dr.-Ing.', '1', 1, 'www.ionas.eu', '1352'),
(14, 'Katz ', 'Marianne', 'Prof. Dr. rer. nat.', '1', 1, 'www.katz.eu', NULL),
(15, 'Klönne ', 'Alfons', 'Prof. Dr.-Ing.', '1', 1, NULL, '1472'),
(16, 'Koblitz ', 'Rudolf', 'Prof. Dr.-Ing.', '1', 1, NULL, '2238'),
(17, 'Köller ', 'Thomas', 'Prof. Dr.-Ing.', '1', 1, NULL, '1464'),
(18, 'Langhammer ', 'Günter', 'Prof. Dr.-Ing.', '1', 1, NULL, '2226'),
(19, 'Litzenburger ', 'Manfred', 'Prof. Dr.-Ing.', '1', 1, NULL, '1516'),
(20, 'Oechsler ', 'Dieter', NULL, '1', 1, NULL, NULL),
(21, 'Quint ', 'Franz', 'Prof. Dr.-Ing.', '1', 1, NULL, '2254'),
(22, 'Ritter ', 'Stefan', 'Prof. Dr. rer. nat.', '1', 1, NULL, '2246'),
(23, 'Sapotta ', 'Hans', 'Prof. Dr.-Ing.', '1', 1, NULL, '2256'),
(24, 'Schäfer ', 'Gerhard', 'Prof. Dr.-Ing.', '1', 1, NULL, '1572'),
(25, 'Scholl ', 'Andreas', 'Dr.', '1', 1, NULL, NULL),
(26, 'Schulz ', 'Guntram', 'Prof.', '1', 1, NULL, '1468'),
(27, 'Sehr ', 'Harald', 'Prof. Dr.', '1', 1, NULL, '1290'),
(28, 'Seifried ', 'Eugen', 'Dipl.-Ing. (FH)', '1', 1, NULL, NULL),
(29, 'Sekinger ', 'Werner', 'Dipl.-Ing. (FH)', '1', 1, NULL, '0721 45949'),
(30, 'Stöckle ', 'Joachim', 'Prof. Dr.-Ing.', '1', 1, NULL, '1574'),
(31, 'Stölting ', 'Juliane', 'Prof. Dr.', '1', 1, NULL, '1366'),
(32, 'Strohrmann ', 'Manfred', 'Prof. Dr.-Ing.', '1', 1, NULL, '2224'),
(33, 'Tübke ', 'Jens', 'Dr.', '1', 1, NULL, NULL),
(34, 'Weizenecker ', 'Jürgen', 'Prof. Dr.', '1', 1, NULL, '1518'),
(35, 'Wolfrum ', 'Klaus', 'Prof. Dr.', '1', 1, 'www.home.hs-karlsruhe.de/~wokl0001/', '1544');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_lectures`
--

CREATE TABLE IF NOT EXISTS `skript_lectures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `edv_id` varchar(20) CHARACTER SET utf8 NOT NULL,
  `semester` tinyint(3) unsigned NOT NULL,
  `course` enum('EB','NB','EEB','ATB','EM') CHARACTER SET utf8 NOT NULL,
  `brief` varchar(10) CHARACTER SET utf8 NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brief` (`brief`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=118 ;

--
-- Daten für Tabelle `skript_lectures`
--

INSERT INTO `skript_lectures` (`id`, `edv_id`, `semester`, `course`, `brief`, `name`) VALUES
(1, 'NB110 ', 1, 'NB', 'hm1_n', 'Höhere Mathematik 1 '),
(2, 'NB121 ', 1, 'NB', 'get1_n', 'Gleichstromtechnik '),
(3, 'NB122 ', 1, 'NB', 'felder_n', 'Felder '),
(4, 'NB131 ', 1, 'NB', 'dig', 'Digitaltechnik '),
(5, 'NB132 ', 1, 'NB', 'digilab', 'Digitallabor '),
(6, 'NB140 ', 1, 'NB', 'info1_n', 'Grundlagen der Informatik 1 '),
(7, 'NB150 ', 1, 'NB', 'phys_n', 'Physik '),
(8, 'NB210 ', 2, 'NB', 'hm2_n', 'Höhere Mathematik 2 '),
(9, 'NB221 ', 2, 'NB', 'get2_n', 'Wechselstromtechnik '),
(10, 'NB222 ', 2, 'NB', 'getlab_n', 'Labor Grundlagen ET '),
(11, 'NB231 ', 2, 'NB', 'mcs_n', 'Mikrocontroller '),
(12, 'NB232 ', 2, 'NB', 'mcslab_n', 'Mikrocontroller-Labor '),
(13, 'NB240 ', 2, 'NB', 'info2_n', 'Grundlagen der Informatik 2 '),
(14, 'NB250 ', 2, 'NB', 'sys_n', 'Systemtheorie '),
(15, 'NB311 ', 3, 'NB', 'hm3_n', 'Höhere Mathematik 3 '),
(16, 'NB312 ', 3, 'NB', 'matlab_n', 'Labor Numerische Mathematik '),
(17, 'NB321 ', 3, 'NB', 'mst_n', 'Messtechnik '),
(18, 'NB322 ', 3, 'NB', 'mstlab_n', 'Labor Messtechnik '),
(19, 'NB331 ', 3, 'NB', 'hls_n', 'Elektronik/HLS '),
(20, 'NB332 ', 3, 'NB', 'hlslab_n', 'Labor Elektronik '),
(21, 'NB340 ', 3, 'NB', 'sss', 'Stochastische Signale und Systeme '),
(22, 'NB350 ', 3, 'NB', 'frs_n', 'Fremdsprache '),
(23, 'NB411 ', 4, 'NB', 'nt1', 'Nachrichtentechnik I '),
(24, 'NB412 ', 4, 'NB', 'ntlab', 'Labor Nachrichtentechnik I '),
(25, 'NB420 ', 4, 'NB', 'hf', 'Hochfrequenztechnik '),
(26, 'NB431 ', 4, 'NB', 'eds', 'Entwurf digitaler Systeme '),
(27, 'NB432 ', 4, 'NB', 'vhdllab', 'VHDL-Labor '),
(28, 'NB441 ', 4, 'NB', 'eas', 'Entwurf analoger Systeme '),
(29, 'NB442 ', 4, 'NB', 'easlab', 'Labor Entwurf analoger Systeme '),
(30, 'NB451 ', 4, 'NB', 'rgt_n', 'Regelungstechnik '),
(31, 'NB452 ', 4, 'NB', 'rgtlab_n', 'Labor Computergestützter Reglerentwurf '),
(32, 'NB611 ', 6, 'NB', 'mnt', 'Methoden der Nachrichtentechnik '),
(33, 'NB622 ', 6, 'NB', 'mfs', 'Mobilfunksysteme '),
(34, 'NB621 ', 6, 'NB', 'ads', 'Algorithmen und Datenstrukturen '),
(35, 'NB622 ', 6, 'NB', 'bus', 'Einführung Bussysteme '),
(36, 'NB631 ', 6, 'NB', 'dsv', 'Digitale Signalverarbeitung '),
(37, 'NB632 ', 6, 'NB', 'dsp', 'Digitale Signalprozessoren '),
(38, 'NB641 ', 6, 'NB', 'le', 'Leistungselektronik '),
(39, 'NB642 ', 6, 'NB', 'emv_n', 'Elektromagmetische Verträglichkeit '),
(40, 'NB661 ', 6, 'NB', 'embeeded', 'Rapid Prototyping für Embedded Systems '),
(41, 'NB662 ', 6, 'NB', 'embeddedla', 'Labor Rapid Prototyping '),
(42, 'NB711 ', 7, 'NB', 'mdim', 'Verarbeitung mehrdimensionaler Signale '),
(43, 'NB712 ', 7, 'NB', 'rnet', 'Rechnernetze '),
(44, 'NB713 ', 7, 'NB', 'oenet', 'Öffentliche Netze '),
(45, 'NB721 ', 7, 'NB', 'maf_n', 'Mitarbeiterführung '),
(46, 'NB722 ', 7, 'NB', 'bwl_n', 'Betriebswirtschaftslehre '),
(47, 'EB110 ', 1, 'EB', 'hm1_e', 'Höhere Mathematik 1 '),
(48, 'EB121 ', 1, 'EB', 'get1_e', 'Gleichstromtechnik '),
(49, 'EB122 ', 1, 'EB', 'felder_e', 'Felder '),
(50, 'EB131 ', 1, 'EB', 'tm', 'Technische Mechanik '),
(51, 'EB132 ', 1, 'EB', 'wk', 'Werkstoffe der Elektrotechnik '),
(52, 'EB140 ', 1, 'EB', 'info1_e', 'Grundlagen der Informatik 1 '),
(53, 'EB150 ', 1, 'EB', 'phys_e', 'Physik '),
(54, 'EB210 ', 2, 'EB', 'hm2_e', 'Höhere Mathematik 2 '),
(55, 'EB221 ', 2, 'EB', 'get2_e', 'Wechselstromtechnik '),
(56, 'EB222 ', 2, 'EB', 'getlab_e', 'Grundlagen ET Labor '),
(57, 'EB231 ', 2, 'EB', 'mcs_e', 'Mikrocontroller '),
(58, 'EB232 ', 2, 'EB', 'mcslab_e', 'Labor Mikrocontrollersysteme '),
(59, 'EB240 ', 2, 'EB', 'info2_e', 'Grundlagen der Informatik 2 '),
(60, 'EB250 ', 2, 'EB', 'frs_e', 'Fremdsprache '),
(61, 'EB311 ', 3, 'EB', 'hm3_e', 'Höhere Mathematik 3 '),
(62, 'EB312 ', 3, 'EB', 'matlab_e', 'Labor Numerische Mathematik '),
(63, 'EB321 ', 3, 'EB', 'mst_e', 'Messtechnik '),
(64, 'EB322 ', 3, 'EB', 'mstlab_e', 'Labor Messtechnik '),
(65, 'EB331 ', 3, 'EB', 'hls_e', 'Elektronik/HLS '),
(66, 'EB332 ', 3, 'EB', 'hlslab_e', 'Labor Elektronik '),
(67, 'EB340 ', 3, 'EB', 'sys_e', 'Systemtheorie '),
(68, 'EB350 ', 3, 'EB', 'em1', 'Elektrische Maschinen 1 '),
(69, 'EB411 ', 4, 'EB', 'te', 'Theoretische Elektrotechnik '),
(70, 'EB412 ', 4, 'EB', 'tds', 'Theorie digitaler Systeme '),
(71, 'EB413 ', 4, 'EB', 'mus', 'Modellbildung und Simulation '),
(72, 'EB421 ', 4, 'EB', 'eev', 'Elektrische Energiesversorgung '),
(73, 'EB422 ', 4, 'EB', 'hv', 'Hochspannungstechnik '),
(74, 'EB430 ', 4, 'EB', 'em2', 'Elektrische Maschinen 2 '),
(75, 'EB441 ', 4, 'EB', 'srt', 'Steuerungstechnik '),
(76, 'EB442 ', 4, 'EB', 'srtlab', 'Labor Steuerungstechnik '),
(77, 'EB451 ', 4, 'EB', 'rgt_e', 'Regelungstechnik '),
(78, 'EB452 ', 4, 'EB', 'rgtlab_e', 'Labor Regelungstechnik '),
(79, 'EB601 ', 6, 'EB', 'mzf', 'Methoden zur Feldberechnung '),
(80, 'EB611 ', 6, 'EB', 'leuet', 'Leistungselektronik/Übertragungstechnik '),
(81, 'EB612 ', 6, 'EB', 'lelab', 'Labor Leistungselektronik '),
(82, 'EB621 ', 6, 'EB', 'thermo', 'Thermodynamik '),
(83, 'EB622 ', 6, 'EB', 'emlab', 'Labor Elektrische Maschinen '),
(84, 'EB631 ', 6, 'EB', 'emv_e', 'Elektromagmetische Verträglichkeit '),
(85, 'EB632 ', 6, 'EB', 'emvlab', 'Labor Elektromagmetische Verträglichkeit '),
(86, 'EB633 ', 6, 'EB', 'hvlab', 'Labor Hochspannungstechnik '),
(87, 'EB641 ', 6, 'EB', 'auto', 'Automatisierungstechnik '),
(88, 'EB642 ', 6, 'EB', 'autolam', 'Labor Automatisierungstechnik '),
(89, 'MTB622', 6, 'EB', 'fem', 'Finite Elemente Methode '),
(90, 'EB711 ', 7, 'EB', 'netplan', 'Planung und Betrieb Belektrischer Netze '),
(91, 'EB712 ', 7, 'EB', 'netlab', 'Elektrische Netze Labor '),
(92, 'EB713 ', 7, 'EB', 'schutz', 'Schutzmaßnahmen in elektrischen Anlagen '),
(93, 'EB721 ', 7, 'EB', 'maf_e', 'Mitarbeiterführung '),
(94, 'EB722 ', 7, 'EB', 'bwl_e', 'Betriebswirtschaftslehre '),
(95, 'EM110 ', 1, 'EM', 'srds', 'Steuerung und Regelung verteilter Systeme '),
(96, 'EM121 ', 1, 'EM', 'pv', 'Prozessvisualisierung '),
(97, 'EM122 ', 1, 'EM', 'bussys', 'Feldbussysteme '),
(98, 'EM130 ', 1, 'EM', 'optik', 'Optische Datenübertragung '),
(99, 'EM140 ', 1, 'EM', 'snt', 'Getaktete Energiewandler '),
(100, 'EM151 ', 1, 'EM', 'emvp', 'EMV-Prüftechnik '),
(101, 'EM152 ', 1, 'EM', 'chemstor', 'Elektrochemische Speicher '),
(102, 'EM161 ', 1, 'EM', 'code', 'Informationstheorie und Codierung '),
(103, 'EM162 ', 1, 'EM', 'digitest', 'Test digitaler Schaltungen '),
(104, 'EM210 ', 2, 'EM', 'sixsigma', 'Toleranzen in vernetzten Systemen '),
(105, 'EM230 ', 2, 'EM', 'ea', 'Elektrische Antriebe '),
(106, 'EM241 ', 2, 'EM', '', '? '),
(107, 'EM242 ', 2, 'EM', 'hvmess', 'Labor Hochspannungsmess- und Prüftechnik '),
(108, 'EM251 ', 2, 'EM', 'rea', 'Rationelle Energieanwendung '),
(109, 'EM252 ', 2, 'EM', 'ee', 'Erneuerbare Energien '),
(110, 'EM262 ', 2, 'EM', 'svsys', 'Signalverarbeitung in Kommunikationssystemen'),
(111, 'EM262 ', 2, 'EM', 'archsys', 'Architekturen von Kommunikationssystemen '),
(112, 'EM271 ', 2, 'EM', 'hfmess', 'Hochfrequenzmesstechnik+Labor '),
(113, 'EM271 ', 2, 'EM', 'hfsys', 'Hochfrequenzsysteme '),
(114, 'EM281 ', 2, 'EM', 'sigguess', 'Signal- und Parameterschätzung '),
(115, 'EM282 ', 2, 'EM', 'anadig', 'Analog-digitale Systeme '),
(116, 'EM291 ', 2, 'EM', 'med', 'Medizintechnik '),
(117, 'EM201 ', 2, 'EM', 'wind', 'Windenergiesysteme ');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_mappings`
--

CREATE TABLE IF NOT EXISTS `skript_mappings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `semester` varchar(4) NOT NULL,
  `lecture` int(10) unsigned NOT NULL,
  `lecturer` int(10) unsigned NOT NULL,
  `document` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `semester` (`semester`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `skript_mappings`
--

INSERT INTO `skript_mappings` (`id`, `semester`, `lecture`, `lecturer`, `document`) VALUES
(1, '11ws', 2, 19, 1),
(2, '11ws', 2, 19, 2),
(3, '12ss', 2, 19, 1),
(4, '12ss', 2, 16, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_orderitems`
--

CREATE TABLE IF NOT EXISTS `skript_orderitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order` int(11) NOT NULL,
  `file` int(11) NOT NULL,
  `format` enum('a4','a5') CHARACTER SET utf8 NOT NULL,
  `duplex` enum('simplex','duplex') CHARACTER SET utf8 NOT NULL,
  `price` decimal(4,2) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_orders`
--

CREATE TABLE IF NOT EXISTS `skript_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester` varchar(4) CHARACTER SET utf8 NOT NULL,
  `user` int(11) NOT NULL,
  `paymentState` enum('unpaid','paid') CHARACTER SET utf8 NOT NULL,
  `deliveryState` enum('ordered','printed','delivered') CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `skript_semesters`
--

CREATE TABLE IF NOT EXISTS `skript_semesters` (
  `code` varchar(4) CHARACTER SET utf8 NOT NULL,
  `name` varchar(20) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Daten für Tabelle `skript_semesters`
--

INSERT INTO `skript_semesters` (`code`, `name`) VALUES
('11ws', 'WS 11/12'),
('12ss', 'SS 12'),
('12ws', 'WS 12/13'),
('13ss', 'SS 13'),
('13ws', 'WS 13/14'),
('14ss', 'SS 14'),
('14ws', 'WS 14/15'),
('15ss', 'SS 15'),
('15ws', 'WS 15/16'),
('16ss', 'SS 16');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
