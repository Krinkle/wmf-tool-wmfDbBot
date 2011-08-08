<?php
/**
 * Abstracted from MediaWiki's Maintenance.php
 */

class Maintenance {
	public static function readlineEmulation( $prompt ) {
		// Fallback... 
		if ( feof( STDIN ) ) {
			return false;
		}
		self::out($prompt, '');
		return fgets( STDIN, 1024 );
	}
	
	public static function readconsole( $prompt = '> ' ) {
		static $isatty = null;
		if ( is_null( $isatty ) ) {
			$isatty = posix_isatty( 0 /*STDIN*/ );
		}
	
		if ( $isatty && function_exists( 'readline' ) ) {
			return readline( $prompt );
		} else {
			if ( $isatty ) {
				$st = self::readlineEmulation( $prompt );
			} else {
				if ( feof( STDIN ) ) {
					$st = false;
				} else {
					$st = fgets( STDIN, 1024 );
				}
			}
			if ( $st === false ) return false;
			$resp = trim( $st );
			return $resp;
		}
	}

	public static function out( $msg = '', $sep = "\n" ) {
		print $msg . $sep;
	}

	public static function end( $msg = '' ) {
		self::out($msg);
		die(1);
	}
}
