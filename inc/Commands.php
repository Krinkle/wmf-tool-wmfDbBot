<?php
/**
 * @author Timo Tijhof, 2011–2013
 * @since 0.1
 * @package wmfDbBot
 */
class Commands {

	/* Variables */
	public static $registry = array(
		'info' => 'getInfo',
		'replag' => 'getReplag',
		'externals' => 'getExternals',
		'externals-update' => 'purgeExternals',
	);

	/* Command functions */

	public static function getInfo( $options = array() ) {
		global $wdbDatabaseInfo, $wdbDefaultSection;

		if ( !isset( $options[0] ) ) {
			return 'Invalid arguments';
		}

		$input = trim( strtolower( $options[0] ) );

		// "DEFAULT"
		if ( $input == 'default' ) {
			$input = $wdbDefaultSection;
		}

		$info = wdbGetInfo( $input );
		$infoLink = wdbAnnotateDefaultSection( $info['section'] );

		switch( $info['type'] ) {
			case 'section':

				$output = array();
				foreach ( $info['relation'] as $dbhost ) {
					$output[] = "$dbhost: {$wdbDatabaseInfo['dbhostToIP'][$dbhost]}";
				}

				$return = join( ', ', $output );
				break;

			case 'dbhost':

				$return = $info['relation'];
				break;

			case 'dbname':

				$output = array();
				foreach ( $info['relation'] as $dbhost ) {
					$output[] = "$dbhost: {$wdbDatabaseInfo['dbhostToIP'][$dbhost]}";
				}

				$return = join( ', ', $output );
				break;

			case 'ip':

				$return = $info['relation'];
				break;

			// case 'unknown':
			default:

				$return = false;
		}

		if ( $return ) {
			if ( $input == $infoLink ) {
				$return = "[$input] $return";
			} else {
				$return = "[$input: $infoLink] $return";
			}
		} else {
			$return = "Unknown identifier ({$input})";
		}


		return $return;
	}

	public static function getReplag( $options = array() ) {
		global $wdbDatabaseInfo;

		$input = isset( $options[0] )
			? trim( strtolower( $options[0] ) )
			: null;

		/* Check all  */

		// "wmfDbBot: replag"
		if ( !$input ) {
			$output = array();
			$replags = getAllReplag( WDB_USE_IGNOREMAX );

			// Build output
			foreach ( $replags as $section => $dbhosts ) {
				$tmp = array();
				foreach ( $dbhosts as $dbhost => $dbhostLag ) {
					$tmp[] = "$dbhost: {$dbhostLag}s";
				}
				$output[] = chr(2) . "[$section] " . chr(2) . implode( ', ', $tmp );
			}

			if ( count( $output ) ) {
				return implode( "; ", $output );
			}

			return 'No replag currently. See also "replag all".';
		}

		// "wmfDbBot: replag all"
		if ( $input === 'all' ) {
			$output = array();
			$replags = getAllReplag( WDB_FORCE_SHOW_ALL );

			// Build output
			foreach ( $replags as $section => $dbhosts ) {
				$tmp = array();
				foreach ( $dbhosts as $dbhost => $dbhostLag ) {
					$tmp[] = "$dbhost: {$dbhostLag}s";
				}
				$output[] = chr(2) . "[$section] " . chr(2) . implode( ', ', $tmp );
			}

			if ( count( $output ) ) {
				// Split in seperate messages
				// s1-s4, s5-s7
				return array(
					implode( '; ', array_slice( $output, 0, 3) ),
					implode( '; ', array_slice( $output, 3 ) ),
				);
			}

			return 'No replag information was available.';
		}


		/* Check one specific id */
		// wmfDbBot: [identifier]

		$info = wdbGetInfo( $input );
		$infoLink = wdbAnnotateDefaultSection( $info['section'] );

		switch( $info['type'] ) {
			case 'section':

				$dbname = wdbDbnameFromSection( $info['section'] );
				if ( !$dbname ) {
					$replags = false;
				} else {
					$replags = getReplagFromDbname( $dbname );
				}

				if ( !$replags ) {
					return 'Could not get replag information.';
				}

				// Show which dbname was used to get the replag
				$infoLink = $dbname;

				$output = array();

				foreach ( $info['relation'] as $dbhost ) {
					// Check whether this is set. We are comparing here:
					// What we believe are dbhosts belonging to this section
					// (according to externals' wmf-config/db.php), and the
					// response of the API. If either has changed since the
					// last sync, we will have missing or additional keys here.
					$output[] = isset( $replags[$dbhost] )
						? "$dbhost: {$replags[$dbhost]}s"
						: "$dbhost (?): -";
				}

				// The above outputs ? for dbhosts that were in the section cluster
				// but are no longer according to api.php
				// The below outputs additional dbhosts that aren't known yet in db.php
				// but are outputted in api.php
				foreach ( $replags as $dbhost => $replag ) {
					// Only output the ones not already output above in $info['relation']
					if ( !in_array( $dbhost, $info['relation'] ) ) {
						$output[] = "$dbhost (!): {$replag}s";
					}
				}

				$return = join( ', ', $output );
				break;

			case 'dbhost':

				$ip = $info['relation'];

				$replags = getReplagFromDbhost( $input );
				if ( !$replags ) {
					return 'Could not get replag information.';
				}

				$return = "$ip: " . $replags[$input] . 's';
				break;

			case 'dbname':

				if ( $input === 'centralauth' ) {
					// Centralauth is not a valid dbname that has a domain with mwroot,
					// but we already have its section in $info, so it can be handled transparently at this point
					$replags = getReplagFromSection( $info['section'] );
				} else {
					$replags = getReplagFromDbname( $input );
				}
				if ( !$replags ) {
					return 'Could not get replag information.';
				}

				$output = array();
				foreach ( $info['relation'] as $dbhost ) {
					if ( isset( $replags[$dbhost] ) ) {
						$output[] = "$dbhost: {$replags[$dbhost]}s";
					} else {
						$output[] = "$dbhost: ?";
					}
				}

				$return = join( ', ', $output );
				break;

			case 'ip':
				$dbhost = $info['relation'];

				$replags = getReplagFromDbhost( $dbhost );
				if ( !$replags ) {
					return 'Could not get replag information.';
				}

				$return = "$dbhost: " . $replags[$dbhost] . 's';
				break;

			// case 'unknown':
			default:

				$return = false;
		}

		if ( $return ) {
			if ( $input == $infoLink ) {
				$return = "[$input] $return";
			} else {
				$return = "[$input: $infoLink] $return";
			}
		} else {
			$return = "Unknown identifier ({$input})";
		}


		return $return;
	}

	public static function getExternals( $options = array() ) {
		global $wdbPath;

		$msgs = array();

		// wmf-operations-mediawiki-config
		chdir( "$wdbPath/externals/wmf-operations-mediawiki-config" );
		$gitHead = trim( exec( 'git rev-parse --verify HEAD' ) );

		$msgs[] = chr(2) . "[operations/mediawiki-config.git]" . chr(2)
			. ' Checked out HEAD: ' . $gitHead
			. ' - ' . 'https://git.wikimedia.org/commit/operations%2Fmediawiki-config.git/' . urlencode( $gitHead );

		return implode( '; ', $msgs );
	}

	public static function purgeExternals( $options = array() ) {
		global $wdbPath;

		ob_start();

		passthru( "php " . escapeshellarg( "$wdbPath/maintenance/updateExternals.php" ) . ";" );
		// After updating it, re-read them into the bot state
		wdbExternals::readExternals();

		$output = ob_get_contents();
		ob_end_clean();

		if ( !$output ) {
			return array(
				'pub' => 'Updating externals failed. An error report has been sent to the commander in private.',
				'priv' => $output,
			);
		} else {
			return array(
				'pub' => 'Successfully updated externals!',
				'priv' => $output,
			);
		}
	}

	/* Do not create an instance of this function */
	private function __construct() {
		return false;
	}

}
