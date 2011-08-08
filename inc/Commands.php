<?php

class Commands {

	/* Variables */
	public static $registry = array(
		'date' => 'getDate',
		'info' => 'getInfo',
		'replag' => 'getReplag',
	);

	/* Command functions */

	public static function getDate( $options = array() ) {
		$format = isset( $options[0] ) && !empty( $options[0] ) ? $options[0] : 'r' ;
		return date( $format );
	}

	public static function getInfo( $options = array() ) {
		global $wdbDatabaseInfo, $wdbDefaultSection;

		if ( !isset( $options[0] ) ) {
			return 'Invalid arguments';
		}

		$options[0] = trim(strtolower($options[0]));

		// "DEFAULT"
		if ( $options[0] == 'default' ) {
			$options[0] = $wdbDefaultSection;
		}

		list( $type, $id, $info, $extra ) = wdbGetInfo( $options[0] );

		switch( $type ) {
		
			case 'section':
				$dbs = array();
				foreach( $info as $dbhost ) {
					$dbs[] = "$dbhost: {$wdbDatabaseInfo['dbhosts'][$dbhost]}";
				}
				$id = wdbResolveDefaultSection( $id );
				$dbs = join( ', ', $dbs );
				$return = "[$id] $dbs";
				break;

			case 'dbhost':
				$extra = wdbResolveDefaultSection ( $extra );
				$return = "[$id: $extra] $info";
				break;

			case 'dbname':
				$dbs = array();
				foreach( $info as $dbhost ) {
					$dbs[] = "$dbhost: {$wdbDatabaseInfo['dbhosts'][$dbhost]}";
				}
				$dbs = join( ', ', $dbs );
				$extra = wdbResolveDefaultSection ( $extra );
				$return = "[$id: $extra] $dbs";
				break;

			default: // case 'unknown':
				$return = "Unknown identifier ({$options[0]})";
		
		}
		
		return $return;
	}

	public static function getReplag( $options = array() ) {
		global $wdbDatabaseInfo;


		/* Check all  */

		// "!replag"
		if ( !isset( $options[0] ) ) {
			$return = array();
			$replag = getAllReplag( WDB_USE_IGNOREMAX );

			// Build output
			foreach ( $replag as $section => $dbhosts ) {
				$tmp = array();
				foreach( $dbhosts as $dbhost=>$dbhostLag ) {
					$tmp[] = "$dbhost: {$dbhostLag}s";
				}
				$return[] = chr(2) . "[$section] " . chr(2) . implode( ', ', $tmp );
			}
			if ( count( $return ) ) {
				return implode( "; ", $return );
			}
			return 'No replag currently. See also "replag all".';
		}

		// "!replag all"
		if ( trim(strtolower($options[0])) == 'all' ) {
			$return = array();
			$replag = getAllReplag( WDB_FORCE_SHOW_ALL );

			// Build output
			foreach ( $replag as $section => $dbhosts ) {
				$tmp = array();
				foreach( $dbhosts as $dbhost=>$dbhostLag ) {
					$tmp[] = "$dbhost: {$dbhostLag}s";
				}
				$return[] = chr(2) . "[$section] " . chr(2) . implode( ', ', $tmp );
			}
			if ( count( $return ) ) {
				// Split in seperate messages
				// s1-s4, s5-s7
				return array(
					implode( '; ', array_slice( $return, 0, 3) ),
					implode( '; ', array_slice( $return, 3) ),
				);
			}
			return 'No replag information was available.';
		}


		/* Check one specific id */

		list( $type, $id, $info, $extra ) = wdbGetInfo( $options[0] );

		switch( $type ) {
		
			case 'section':

				$replag = getReplagFromSection( $id );
				if ( !$replag ) {
					return 'Could not get replag information.';
				}

				// Build output
				$outputInfo = array();
				foreach( $info as $dbhost ) {
					$outputInfo[] = "$dbhost: {$replag[$dbhost]}s";
				}

				// Return
				$outputInfo = join( ', ', $outputInfo );
				$return = "[$id] $outputInfo";
				break;

			case 'dbhost':

				$replag = getReplagFromDbhost( $id );
				if ( !$replag ) {
					return 'Could not get replag information.';
				}

				// Build output
				$outputInfo = "$id: {$replag[$id]}s";

				// Return
				$return = "[$id] $outputInfo";
				break;

			case 'dbname':

				// Centralauth is not a wiki
				if ( $id == 'centralauth' ) {
					// Get centralauth-section
					$tmp = wdbGetInfo('centralauth');
					$section = $tmp[3];
					$replag = getReplagFromSection( $section );
				} else {
					$replag = getReplagFromDbname( $id );
				}
				if ( !$replag ) {
					return 'Could not get replag information.';
				}

				// Build output
				$outputInfo = array();
				foreach( $info as $dbhost ) {
					$outputInfo[] = "$dbhost: {$replag[$dbhost]}s";
				}

				// Return
				$outputInfo = join( ', ', $outputInfo );
				$return = "[$id: $extra] $outputInfo";
				break;

			default: // case 'unknown':
				$return = 'Unknown identifier';
		
		}
		
		return $return;

	}

	/* Do not create an instance of this function */
	private function __construct(){
		return false;
	}

}


