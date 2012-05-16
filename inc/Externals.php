<?php
/**
 * @author Timo Tijhof, 2011
 * @since 0.1
 * @package wmfDbBot
 */

/* Populate from externals */

$wdbExternals['db.php'] = wdbLoadPhpFile(
	"$wdbPath/external/db.php",
	array( 'wgLBFactoryConf' ),
	array( 'wgDBname', 'wgDBuser', 'wgDBpassword' ),
	array( 'DBO_DEFAULT' )
);

$wdbExternals['all.dblist'] = wdbLoadFlatFile( "$wdbPath/external/all.dblist" );

// To be populated later. Wiki data table rows by dbname
$wdbExternals['toolserver.wiki'] = array();


/* Process externals */

// Wiki data
$tmp = new stdClass();
$tmp->connect = mysql_connect( 'sql.toolserver.org', $wdbTsDbUser, $wdbTsDbPassword );
if ( $tmp->connect ) {
	$tmp->result = mysql_query(
		"SELECT * FROM toolserver.wiki"
		. " WHERE lang IS NOT NULL"
		. " AND family IS NOT NULL",
		$tmp->connect
	);
	if ( $tmp->result ) {
		$tmp->wikiData = array();
		while( $row = mysql_fetch_object( $tmp->result ) ) {
			// Some domains are missing...
			if ( empty( $row->domain ) ) {
				$row->domain = "{$row->lang}.{$row->family}.org";
			}
			// Here dbname is including "_p" suffix
			$tmp->wikiData[$row->dbname] = $row;
		}
	}
}
$wdbExternals['toolserver.wiki'] = $tmp->wikiData;
unset( $tmp );


// wdbDatabaseInfo
global $wdbDatabaseInfo;
$wdbDatabaseInfo = array(
	'sections' => array( /*
		's1' => array(
			'db36' => ...,
			'db32' => ...,
		),
		...
	*/ ),
	'dbhosts' => array( /*
		'db1' => '0.0.0.0',
		...
	*/ ),
	'dbnames' => array( /*
		'enwiki' => 's1',
		..
	*/ ),
	// Random wiki for each section
	'sectionToWiki' => array( /*
		's1' => 'enwiki',
		...
		*/
		// Prepopulate the default section > random wiki
		// since these won't be looped over in the loop that follows,
		// because the external config only contains non-default section wikis.
		$wdbDefaultSection => $wdbDefaultSectionWiki,
	),
);

if ( wdbGetExternalVar( 'db.php', 'wgLBFactoryConf' ) && $wdbExternals['all.dblist'] ) {

	$factoryConf = wdbGetExternalVar( 'db.php', 'wgLBFactoryConf' );

	$wdbDatabaseInfo['sections'] = $factoryConf['sectionLoads'];
	// default has array key 'DEFAULT', it's not in the db.php array as 's3'
	$wdbDatabaseInfo['sections'][$wdbDefaultSection] = $wdbDatabaseInfo['sections']['DEFAULT'];
	ksort( $wdbDatabaseInfo['sections'] );

	$wdbDatabaseInfo['dbhosts'] = $factoryConf['hostsByName'];

	// Load all dbnames
	$wdbDatabaseInfo['dbnames'] = array_fill_keys(
		array_values( $wdbExternals['all.dblist'] ),
		'DEFAULT'
	);
	// Overwrite non-default
	foreach( $factoryConf['sectionsByDB'] as $dbname => $section ) {
		$wdbDatabaseInfo['dbnames'][$dbname] = $section;
		$wdbDatabaseInfo['sectionToWiki'][$section] = $dbname;
	}
	unset( $dbname, $section );
	
} else {
	die( 'Unexpected error in db.php and/or all.dblist' );
}
