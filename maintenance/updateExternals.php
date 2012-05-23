<?php
/**
 * @author Timo Tijhof, 2011
 * @since 0.1
 * @package wmfDbBot
 */

require_once( __DIR__ . '/../inc/InitConfig.php' );

function logLine($msg = '') {
	global $wdbPath;
	$msg = '[' . gmdate( 'r' ) . '] ' . trim( $msg ) . "\n";

	print $msg;

	$logFilePath = "$wdbPath/logs/updateExternals.log";
	file_put_contents( $logFilePath, $msg, FILE_APPEND | LOCK_EX );
}

logLine( 'Updating externals ...' );


/**
 * operations/mediawiki-config.git
 */
$extName = 'wmf-operations-mediawiki-config';
logLine( "$extName:" );

chdir( $wdbPath . '/externals/' . $extName );
foreach( array(
	'git remote update;',

	'git reset --hard;',
	'git clean -d -x --force;',
	'git checkout master;',

	'git reset --hard remotes/origin/master;',
	'git clean -d -x --force;',
) as $cmd ) {
	print "* $cmd\n";;
	passthru( $cmd );
}

logLine( "$extName: Finished." );


logLine( 'End of script.' );
