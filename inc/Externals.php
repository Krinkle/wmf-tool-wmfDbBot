<?php
/**
 * @author Timo Tijhof, 2011
 * @since 0.1
 * @package wmfDbBot
 */

class wdbExternals {

	public static function readExternals() {
		global $wdbPath, $wdbExternals, $wdbDatabaseInfo, $wdbTsGetWikiAPI,
			$wdbDefaultSection;

		/**
		 * Load wmf-operations-mediawiki-config
		 */
		$wmfOpsMwConfigRepo = "$wdbPath/externals/wmf-operations-mediawiki-config";
		$wdbExternals['db.php'] = wdbLoadPhpFile(
			// TODO: Looks like we can't request replag from the API
			// for both data centers. Even when forging the Host header
			// and request with cURL to <project>-lb.pmtpa.wikimedia.org directly
			// it always responds from eqiad.
			// So for now only read db-eqiad settings and assume that eqiad is
			// handling the request.
			"$wmfOpsMwConfigRepo/wmf-config/db-eqiad.php",
			array( 'wgLBFactoryConf' ),
			array( 'wgDBname', 'wgDBuser', 'wgDBpassword' ),
			array( 'DBO_DEFAULT' )
		);
		$wdbExternals['all.dblist'] = wdbLoadFlatFile( "$wmfOpsMwConfigRepo/all.dblist" );

		// To be populated later. Wiki data table rows by dbname
		$wdbExternals['wikiinfo'] = array();

		/**
		 * Load wikiinfo
		 */
		$dbChunkSize = 107;
		$dbListChunks = array_chunk( $wdbExternals['all.dblist'], $dbChunkSize );
		$chunks = count( $dbListChunks );
		$wikis = count( $wdbExternals['all.dblist'] );
		$errors = 0;
		$requestFail = false;
		print 'Initializing GetWikiAPI external data for '
			. "$wikis wikis. Devided into $chunks chunks of up to $dbChunkSize wikis...\n";
		foreach ( $dbListChunks as $i => $dbListChunk ) {
			$apiRequest = $wdbTsGetWikiAPI . '?' . http_build_query(array(
				'format' => 'json',
				'wikiids' => implode( '|', $dbListChunk ),
			));
			$printPrefix = "- (Request " . ($i+1) . "/$chunks): ";
			print $printPrefix . substr( $apiRequest, 0, 100 ) . "..\n";
			$apiResponse = wdbSimpleCurlGetContent( $apiRequest );
			if ( !$apiResponse ) {
				$requestFail = true;
				print $printPrefix . "Invalid response\n";
				foreach ( $dbListChunk as $db ) {
					$wdbExternals['wikiinfo'][$db] = false;
				}
			} else {
				$apiData = json_decode( $apiResponse, /* assocArray = */ false );
				foreach ( $dbListChunk as $db ) {
					if ( !isset( $apiData->$db ) || !isset( $apiData->$db->data ) ) {
						$wdbExternals['wikiinfo'][$db] = false;
						$errors++;
					} else {
						$wdbExternals['wikiinfo'][$db] = $apiData->$db->data;
					}
				}
			}
		}
		if ( $errors ) {
			print "The API failed to give valid data for $errors of $wikis wiki identifiers.\n";
		}
		print "Finished initializing GetWikiAPI data\n";

		/**
		 * Init wdbDatabaseInfo
		 */
		$wdbDatabaseInfo = array(
			'sectionToDbhosts' => array( /*
				's1' => array( 'db36', 'db32', ...),
				...
			*/ ),
			'dbhostToSection' => array( /*
				'db36' => 's1',
				'db32' => 's1',
				...
			*/ ),
			'dbhostToIP' => array( /*
				'db1' => '0.0.0.0',
				...
			*/ ),
			'ipToDbhost' => array( /*
				'0.0.0.0' => 'db1',
				...
			*/ ),
			'dbnameToSection' => array( /*
				'enwiki' => 's1',
				..
			*/ ),

			// Random sample wiki for each section, used to get the replag data
			// by making a request to the API of a wiki that has it's db in that section
			'sectionToWiki' => array( /*
				's1' => 'enwiki',
				...
			*/ ),
		);

		/**
		 * Post-process information and populate wdbDatabaseInfo
		 */
		if ( wdbGetExternalVar( 'db.php', 'wgLBFactoryConf' ) && $wdbExternals['all.dblist'] ) {

			$factoryConf = wdbGetExternalVar( 'db.php', 'wgLBFactoryConf' );

			$wdbDatabaseInfo['sectionToDbhosts'] = $factoryConf['sectionLoads'];

			// db.php has array key 'DEFAULT' for the default section in sectionLoad,
			// not the actual section key. We need to find them by section key later on, so move
			// that array over to the right 's#' key in the 'sections' array.
			$wdbDatabaseInfo['sectionToDbhosts'][$wdbDefaultSection] = $wdbDatabaseInfo['sectionToDbhosts']['DEFAULT'];
			// Also, don't forget to drop the other one. Otherwise "replag all" will either fail to find the
			// inexisting section named 'DEFAULT', or the default section could get listed twice.
			unset( $wdbDatabaseInfo['sectionToDbhosts']['DEFAULT'] );

			// Populate dbhostTosection
			// And continue sectionToDbhosts
			foreach ( $wdbDatabaseInfo['sectionToDbhosts'] as $section => $dbhostData ) {
				$wdbDatabaseInfo['sectionToDbhosts'][$section] = array_keys( $dbhostData );
				foreach ( $dbhostData as $dbhost => $data ) {
					$wdbDatabaseInfo['dbhostToSection'][$dbhost] = $section;
				}
			}

			// Populate dbhostToIP
			$wdbDatabaseInfo['dbhostToIP'] = $factoryConf['hostsByName'];

			// Populate ipToDbhost
			$wdbDatabaseInfo['ipToDbhost'] = array_flip( $factoryConf['hostsByName'] );

			// Populate dbnameToSection
			// By default map them all dbnames to the default section.
			$wdbDatabaseInfo['dbnameToSection'] = array_fill_keys(
				array_values( $wdbExternals['all.dblist'] ),
				$wdbDefaultSection
			);

			// Populate sectionToWiki
			// Also continue dbnameToSection: overwrite the non-defaults.
			foreach ( $factoryConf['sectionsByDB'] as $dbname => $section ) {
				$wdbDatabaseInfo['sectionToWiki'][$section] = $dbname;
				$wdbDatabaseInfo['dbnameToSection'][$dbname] = $section;
			}

			// Continue sectionToWiki
			// Find a sample wiki for 'DEFAULT' by looping through and using the first
			// we get that isn't set.
			foreach ( $wdbDatabaseInfo['dbnameToSection'] as $dbname => $section ) {
				if ( $section === $wdbDefaultSection ) {
					$wdbDatabaseInfo['sectionToWiki'][$wdbDefaultSection] = $dbname;
					// We only need one.
					break;
				}
			}

			ksort( $wdbDatabaseInfo['sectionToDbhosts'] );
			ksort( $wdbDatabaseInfo['sectionToWiki'] );

			if ( !isset( $wdbDatabaseInfo['sectionToWiki'][$wdbDefaultSection] ) ) {
				throw new Exception( 'No wikis found in the default section. We need at least 1 sample wiki to check the replag of the default section.' );
			}

		} else {
			throw new Exception( 'Unexpected error in db.php and/or all.dblist' );
		}
	}
}
