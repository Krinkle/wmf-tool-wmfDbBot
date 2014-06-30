<?php
/**
 * @author Timo Tijhof, 2011–2013
 * @since 0.1
 * @package wmfDbBot
 */

function wdbLoadPhpFile( $path, $extractVars = array(),
	$dependencyGlobals = array(), $dependencyDefines = array() ) {
	// Verify existance
	if ( !file_exists( $path ) ) {
		throw new Exception( __FUNCTION__ . " could not find: $path" );
	}

	// Create base array with all expected variables set to null
	$base = array_fill_keys( $extractVars, null );

	// Dependancies (fix E_NOTICE)
	foreach( $dependencyGlobals as $depGlobal ) {
		if ( !isset( $GLOBALS[$depGlobal] ) ) {
			$$depGlobal = '';
		}
	}
	foreach( $dependencyDefines as $depDefine ) {
		if ( !defined( $depDefine ) ) {
			define( $depDefine, 1 );
		}
	}

	// Catch the flying variables into $extracted
	include( $path );
	$extracted = compact( $extractVars );

	return array_merge( $base, $extracted );
}

/**
 * Based on:
 * https://git.wikimedia.org/blob/operations%2Fmediawiki-config.git/19fd296/refresh-dblist#L41
 */
function wdbLoadFlatFile( $path ) {
	// Verify existance
	if ( !file_exists( $path ) ) {
		throw new Exception( __FUNCTION__ . " could not find: $path" );
	}

	return array_filter( array_map( 'trim', file( $path ) ) );
}

function wdbGetExternalVar( $id, $var, $fallback = null ) {
	global $wdbExternals;
	if ( isset( $wdbExternals[$id] ) && isset( $wdbExternals[$id][$var] ) ) {
		return $wdbExternals[$id][$var];
	} else {
		return $fallback;
	}
}


function wdbArrayDig( /* $array, $key[0], $key[1], ...  */ ) {
	$keys = func_get_args();
	$array = array_shift( $keys );

	if ( !is_array( $array ) ) {
		return $array;
	}
	$hunt = $array;
	while( $nextkey = array_shift($keys) ){
		if ( isset( $hunt[$nextkey] ) ) {
			$hunt = $hunt[$nextkey];
		} else {
			$hunt = array();
			break;
		}
	}
	return $hunt;

}

function wdbGlobalsNotNull( $varNames ) {
	foreach( $varNames as $varName ) {
		if ( !isset( $GLOBALS[$varName] ) ) {
			return $varName;
		}
	}
	return true;
}

function wdbProccessRawCommand( $input = null ) {
	static $d = null;
	static $quits = array( 'quit', 'die', 'exit' );
	if ( is_null( $d ) ) {
		$d = new stdClass();
		$d->raw = null;
		$d->fnName = null;
		$d->fnArgs = array();
		$d->isQuit = false;
		$d->isError = false;
	}
	$command = $d;

	if ( is_null( $input ) || !isset( $input ) ) {
		return $command;

	} elseif ( $input === false || !is_string( $input ) ) {
		$command->isError = true;
		return $command;

	} elseif ( in_array( $input, $quits ) ) {
		$command->isQuit = true;
		return $command;

	} else {
		list( $function, $args ) = wdbParseCommand( $input );
		$command->fnName = $function;
		$command->fnArgs = $args;
		return $command;
	}

}

function wdbParseCommand( $input ) {
	if ( !is_string( $input ) ) {
		$input = '';
	}

	// Trim input. Could contain trailing spaces or (in case of cli) a \n
	$input = trim( $input );

	$split = explode( ' ', $input, 2 );

	$function = $split[0];
	$args = isset( $split[1] ) ? explode( ' ', $split[1] ) : array();

	return array( $function, $args );
}

function wdbExecuteCommand( $command ) {
	if ( isset( Commands::$registry[$command->fnName] ) ) {
		$fn = Commands::$registry[$command->fnName];
		return Commands::$fn( $command->fnArgs );

	} else {
		return "Unknown command: {$command->fnName}.";
	}

}

function wdbAnnotateDefaultSection( $section ) {
	global $wdbDefaultSection;
	if ( $section == 'DEFAULT' ) {
		return "$section ($wdbDefaultSection)";
	} elseif ( $section == $wdbDefaultSection ) {
		return "$wdbDefaultSection (DEFAULT)";
	}
	return $section;
}


/** @return array */
function wdbGetInfo( $id ) {
	global $wdbDatabaseInfo;

	$needle = trim( strtolower( $id ) );

	// Check section
	if ( isset( $wdbDatabaseInfo['sectionToDbhosts'][$needle] ) ) {

		$dbhosts = $wdbDatabaseInfo['sectionToDbhosts'][$needle];

		return array(
			'type'     => 'section',
			'input'    => $needle,
			'relation' => $dbhosts,
			'section'  => $needle,
		);

	// Check dbhost
	} elseif ( isset( $wdbDatabaseInfo['dbhostToIP'][$needle] ) ) {

		$ip =  $wdbDatabaseInfo['dbhostToIP'][$needle];

		$section = '?';
		foreach( $wdbDatabaseInfo['sectionToDbhosts'] as $dbhostSection => $dbhosts ) {
			if ( in_array( $needle, $dbhosts ) ) {
				$section = $dbhostSection;
				break;
			}
		}

		return array(
			'type'     => 'dbhost',
			'input'    => $needle,
			'relation' => $ip,
			'section'  => $section,
		);

	// Check dbname
	} elseif ( isset( $wdbDatabaseInfo['dbnameToSection'][$needle] ) ) {

		$section = $wdbDatabaseInfo['dbnameToSection'][$needle];
		$dbhosts = $wdbDatabaseInfo['sectionToDbhosts'][$section];

		return array(
			'type'     => 'dbname',
			'input'    => $needle,
			'relation' => $dbhosts,
			'section'  => $section,
		);

	// Check dbhost IP
	} elseif ( isset( $wdbDatabaseInfo['ipToDbhost'][$needle] ) ) {

		$dbhost = $wdbDatabaseInfo['ipToDbhost'][$needle];
		$section = isset( $wdbDatabaseInfo['dbhostToSection'][$dbhost] )
			? $wdbDatabaseInfo['dbhostToSection'][$dbhost]
			: '?';

		return array(
			'type'     => 'ip',
			'input'    => $needle,
			'relation' => $dbhost,
			'section'  => $section,
		);

	// Else: unknown
	} else {
		return array(
			'type'     => 'unknown',
			'input'    => $needle,
			'relation' => null,
			'section'  => null,
		);
	}

}

function wdbSimpleCurlGetContent( $url ) {
	global $wdbUserAgent;

	print "wdbSimpleCurlGetContent: $url\n";

	$ch = curl_init();
	curl_setopt_array( $ch, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_USERAGENT => $wdbUserAgent,
		CURLOPT_URL => $url,
	) );
	$raw = curl_exec( $ch );

	if ( curl_errno( $ch ) ) {
		return curl_errno( $ch );
	}

	return $raw;
}

// dbhost > section > (random)dbname > mwroot > api-replag
function getReplagFromDbhost( $dbhost ) {
	$info = wdbGetInfo( $dbhost );
	$section = $info['section'];
	return getReplagFromSection( $section );
}

// section > (random)dbname > mwroot > api-replag
function getReplagFromSection( $section ) {
	$dbname = wdbDbnameFromSection( $section );
	if ( $dbname ) {
		return getReplagFromDbname( $dbname );
	} else {
		return false;
	}
}

// dbname > mwroot > api-replag
function getReplagFromDbname( $dbname ) {
	$mwRoot = wdbMwRootFromDbname( $dbname );
	if ( $mwRoot ) {
		return getReplagFromMWRoot( $mwRoot );
	}
	return false;
}

function wdbDbnameFromSection( $section ) {
	global $wdbDatabaseInfo;
	return isset( $wdbDatabaseInfo['sectionToWiki'][$section] ) ?
		$wdbDatabaseInfo['sectionToWiki'][$section] :
		false;
}

function wdbMwRootFromDbname( $dbname ) {
	$info = wdbGetExternalVar( 'wikiinfo', $dbname );
	if ( !$info ) {
		return false;
	}
	return $info->canonicalserver . $info->scriptpath;
}

/**
 * Get replag from the API
 *
 * @param $mwRoot string Url to the wiki root
 * @return array Keys: dbhosts, values: lag in seconds
 */
function getReplagFromMWRoot( $mwRoot = 'http://meta.wikimedia.org/w' ) {
	static $apiQuery = array(
		'format' => 'json',
		'action' => 'query',
		'meta' => 'siteinfo',
		'siprop' => 'dbrepllag',
		'sishowalldb' => '1',
	);
	$apiUrl = "{$mwRoot}/api.php?" . http_build_query( $apiQuery );

	// Get data
	$apiReturn = wdbSimpleCurlGetContent( $apiUrl );
	if ( !$apiReturn ) {
		return 'no: ' . $apiReturn;
	}
	$apiResult = json_decode( $apiReturn, /* assoc = */ true );
	if ( !$apiResult ) {
		return 'false: ' . $apiReturn;
	}
	$dbreplicators = wdbArrayDig( $apiResult, 'query', 'dbrepllag' );

	/**
	 * Transform:
	 *  array( 0 => array( 'host' => 'name', 'lag' => 1 ), 1 => ... );
	 * ..into:
	 *  array( 'name' => 1, 'othername' => 0 );
	 */
	$return = array();
	foreach( $dbreplicators as $dbreplicator ) {
		$return[$dbreplicator['host']] = $dbreplicator['lag'];
	}
	return $return;
}

function getAllReplag( $hide = WDB_USE_IGNOREMAX ) {
	global $wdbDatabaseInfo, $wdbReplagThreshold;

	$all = array();
	$replag = null;

	foreach( $wdbDatabaseInfo['sectionToDbhosts'] as $section => $sectionDbhosts ) {
		$replag = getReplagFromSection( $section );
		if ( $replag ) {
			foreach( $replag as $dbhost => $dbhostReplag ) {
				if ( $hide == WDB_FORCE_SHOW_ALL || intval($dbhostReplag) > $wdbReplagThreshold ) {
					$all[$section][$dbhost] = $dbhostReplag;
				}
			}
		}
	}
	return $all;
}
