<?php


function wdbLoadPhpFile( $path, $extractVars = array(),
	$dependancyGlobals = array(), $dependancyDefines = array() ) {
	// Verify existance
	if ( !file_exists( $path ) ) {
		return false;
	}

	// Create base array with all expected variables set to null
	$base = array_fill_keys( $extractVars, null );

	// Dependancies (fix E_NOTICE)
	foreach( $dependancyGlobals as $depGlobal ) {
		if ( !isset( $GLOBALS[$depGlobal] ) ) {
			$GLOBALS[$depGlobal] = null;
		}
		global $$depGlobal;
	}
	foreach( $dependancyDefines as $depDefine ) {
		if ( !defined( $depDefine ) ) {
			define( $depDefine, 1 );
		}
	}

	// Catch the flying variables into $extracted
	include( $path );
	$extracted = compact( $extractVars );

	return array_merge( $base, $extracted );
}

function wdbLoadFlatFile( $path ) {
	// Verify existance
	if ( !file_exists( $path ) ) {
		return false;
	}

	$lines = array_map( 'trim', file( $path ) );

	return $lines;
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
	@list( $function, $args ) = explode( ' ', $input, 2 );
	return array( $function, explode( ' ', $args ) );
}

function wdbExecuteCommand( $command ) {
	if ( isset( Commands::$registry[$command->fnName] ) ) {
		$fn = Commands::$registry[$command->fnName];
		return Commands::$fn( $command->fnArgs );
	
	} else {
		return "Unknown command: {$command->fnName}.";
	}

}

function wdbResolveDefaultSection( $section ) {
	global $wdbDefaultSection;
	if ( $section == 'DEFAULT' ) {
		return "$section ($wdbDefaultSection)";
	} elseif ( $section == $wdbDefaultSection ) {
		return "$wdbDefaultSection (DEFAULT)";
	}
	return $section;
}


function wdbGetInfo( $id ) {
	global $wdbDatabaseInfo;

	$needle = trim( strtolower( $id ) );

	// Check sections
	if ( isset( $wdbDatabaseInfo['sections'][$needle] ) ) {

		// array(db1, db2, ...)
		$dbhosts = array_keys( $wdbDatabaseInfo['sections'][$needle] );

		return array( 'section', $needle, $dbhosts, null );

	// Check db hosts
	} elseif ( isset( $wdbDatabaseInfo['dbhosts'][$needle] ) ) {

		// 0.0.0.0
		$ip =  $wdbDatabaseInfo['dbhosts'][$needle];

		// Get section
		$section = '?';
		foreach( $wdbDatabaseInfo['sections'] as $section => $dbhosts ) {
			if ( isset( $dbhosts[$needle] ) ) {
				break;
			}
		}

		return array( 'dbhost', $needle, $ip, $section );

	// Check db names
	} elseif ( isset( $wdbDatabaseInfo['dbnames'][$needle] ) ) {

		// Get the section as extra info
		$section = $wdbDatabaseInfo['dbnames'][$needle];

		// array(db1, db2, ...)
		$dbhosts = array_keys( $wdbDatabaseInfo['sections'][$section] );

		return array( 'dbname', $needle, $dbhosts, $section );

	// Else: unknown
	} else {
		return array( 'unknown', $needle, null, null );
	}

}

function wdbSimpleCurlGetContent( $url ) {
	static $curlOpt = null;

	if ( is_null( $curlOpt ) ) {
		global $wdbUserAgent;
		$curlOpt = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT => $wdbUserAgent,
		);
	}

	$ch = curl_init();
	curl_setopt_array( $ch, $curlOpt );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$raw = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		return curl_errno( $ch );
	}
	return $raw;
}

// dbhost > section > (random)dbname > mwroot > api-replag
function getReplagFromDbhost( $dbhost ) {
	$info = wdbGetInfo( $dbhost );
	$section = $info[3];
	return getReplagFromSection( $section );
}

// section > (random)dbname > mwroot > api-replag
function getReplagFromSection( $section ) {
	global $wdbDatabaseInfo;
	$dbname = isset( $wdbDatabaseInfo['sectionToWiki'][$section] ) ?
		$wdbDatabaseInfo['sectionToWiki'][$section] : null;
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

function wdbMwRootFromDbname( $dbname ) {
	$wikidata = wdbGetExternalVar( 'toolserver.wiki', "{$dbname}_p" );
	if ( !$wikidata ) {
		return false;
	}
	return "http://{$wikidata->domain}{$wikidata->script_path}";
}

/**
 * Get replag from the API
 *
 * @param $mwRoot string Url to the wiki root
 * @return array Keys: dbhosts, values: lag in seconds
 */
function getReplagFromMWRoot( $mwRoot = 'http://meta.wikimedia.org/w/' ) {
	static $apiQuery = array(
		'format' => 'php',
		'action' => 'query',
		'meta' => 'siteinfo',
		'siprop' => 'dbrepllag',
		'sishowalldb' => true,
	);
	$apiUrl = "{$mwRoot}api.php?" . http_build_query( $apiQuery );

	// Get data
	$apiReturn = wdbSimpleCurlGetContent( $apiUrl );
	if ( !$apiReturn ) {
		return 'no: ' . $apiReturn;
	}
	$apiResult = unserialize( $apiReturn );
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
	global $wdbDatabaseInfo, $wdbDefaultMaxIgnoreLag;
	
	$all = array();
	$replag = null;

	foreach( $wdbDatabaseInfo['sections'] as $section => $sectionDbhosts ) {
		$replag = getReplagFromSection( $section );
		if ( $replag ) {
			foreach( $replag as $dbhost => $dbhostReplag ) {
				if ( $hide == WDB_FORCE_SHOW_ALL || intval($dbhostReplag) > $wdbDefaultMaxIgnoreLag ) {
					$all[$section][$dbhost] = $dbhostReplag;
				}			
			}
		}
	}
	return $all;
}
