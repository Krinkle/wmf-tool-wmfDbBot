<?php
/**
 * This file initializes wmfDbBot.
 * This is the only valid point of entry.
 *
 * @author Timo Tijhof, 2011
 * @since 0.1
 * @package wmfDbBot
 */

// Minimum PHP version
if ( !function_exists( 'version_compare' ) || version_compare( phpversion(), '5.3.2' ) < 0 ) {
	echo "<b>wmfDbBot Fatal:</b> wmfDbBot requires at least PHP 5.3.2\n";
	exit;
}

// Config
require_once( __DIR__ . '/inc/InitConfig.php' );

// Initialize globals
$wdbRequiredConfig = array(
	'wdbPath',
	'wdbContact',
	'wdbDefaultSection',
	'wdbDefaultSectionWiki',
	'wdbTsDbUser',
	'wdbTsDbPassword',
);
$wdbExternals = array();
$wdbDatabaseInfo = null;
$wdbUserAgent = "WmfDbBot/$wdbVersion Contact/$wdbContact";

// Load stuff
require_once( "$wdbPath/inc/Maintenance.php" );
require_once( "$wdbPath/inc/Functions.php" );
if ( ( $undefined = wdbGlobalsNotNull( $wdbRequiredConfig ) ) !== true ) {
	die( '[Fatal error] One or more required variables was'
	 . " not defined in LocalConfig ($undefined).\n" );
}
require_once( "$wdbPath/inc/Commands.php" );
require_once( "$wdbPath/inc/Externals.php" );
require_once( "$wdbPath/inc/libs/Args.php" );
