<?php
/**
 * Options for the printservice plugin
 *
 * @author Florian Rinke <rinke.florian@web.de>
 */


//$meta['fixme'] = array('string');

$meta ['db_server'] = array ('string' );
$meta ['db_user'] = array ('string' );
$meta ['db_password'] = array ('password' );
$meta ['db_database'] = array ('string' );
$meta ['db_prefix'] = array ('string' );
$meta ['semester'] = array ('multichoice', '_choices' => array ('11ws', '12ss', '12ws', '13ss', '13ws', '14ss', '14ws', '15ss', '15ws', '16ss' ) );
$meta ['active'] = array ('onoff' );
$meta ['pagecost'] = array ('string' );