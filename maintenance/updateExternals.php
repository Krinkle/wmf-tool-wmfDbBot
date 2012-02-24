<?php

require_once( __DIR__ . '/../inc/DefaultConfig.php' );
require_once( "$wdbPath/LocalConfig.php" );

function logLine($msg = '') {
	global $wdbPath;
	$msg = '[' . gmdate( 'r' ) . '] ' . trim($msg)."\n";

	echo $msg;

	$logFilePath = "$wdbPath/logs/updateExternals.log";
	file_put_contents($logFilePath, $msg, FILE_APPEND | LOCK_EX);
}

logLine( 'Attempting to  externals ..' );


$externals = array(
	'db.php' => array(
		'remote' => 'http://noc.wikimedia.org/conf/db.php.txt',
		'local' => "$wdbPath/external/db.php",
	),
	'all.dblist' => array(
		'remote' => 'http://noc.wikimedia.org/conf/all.dblist',
		'local' => "$wdbPath/external/all.dblist",
	),
);

foreach ( $externals as $extName => $ext ) {

	logLine( "$extName:" );
	logLine( "- Downloading remote from: {$ext['remote']}" );
	$raw = file_get_contents( $ext['remote'] );
	
	if ( $raw  && strlen( $raw ) > 3 ) {
		logLine( "- Downloaded succeeded" );
		logLine( "- Saving to: {$ext['local']}" );

		$stat = file_put_contents( $ext['local'], $raw );
		if ( $stat ) {
			logLine( "- Save succeeded" );
		} else {
			logLine( "- Save FAILED" );
		}

	} else {
		logLine( "- Download FAILED" );
	}

}

logLine( 'End of script.' );
