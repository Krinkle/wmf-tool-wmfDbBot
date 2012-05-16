<?php
/**
 * @author Timo Tijhof, 2011
 * @since 0.1
 * @package wmfDbBot
 */

require_once( __DIR__ . '/InitBot.php' );

// Must be ran from CLI
if ( !isset( $argc ) || !isset( $argv ) || empty( $argc ) || empty( $argv ) ) {
	die( '[Fatal error] ' . basename(__FILE__)
	 . " must be ran from the command line interface directly!\n" );
}

// Debug
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

// Start main application
while( ( $input = Maintenance::readconsole() ) !== false ) {
	$command = wdbProccessRawCommand( $input );
	if ( $command->isQuit ) {
		break;
	} elseif ( $command->isError ) {
		Maintenance::out( 'An unexpected error occurred. Aborting...' );
		break;
	} else {
		// Can return 1 msg or array of messages
		$msgs = wdbExecuteCommand( $command );
		$msgs = (array)$msgs;
		foreach ( $msgs as $msg ) {
			Maintenance::out( "<$wdbNickname>: $msg" );
		}
	}
}


// Finally
Maintenance::end( "\n--End of application has been reached\nBye!\n" );
