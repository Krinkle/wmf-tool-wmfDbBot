<?php

/**
 * General
 */
// User agent etc.
$wdbContact  = 'krinklemail@gmail.com';

/**
 * Config for externals
 */
// Server config: Default section name for wikis without a section or in section 'DEFAULT'
$wdbDefaultSection = 's3';

// Server config: Any wiki that is in the default section, used to get replag for 'DEFAULT'
$wdbDefaultSectionWiki = 'mediawikiwiki';

// Database access
$mycnf = parse_ini_file( '/home/krinkle/.my.cnf' );
$wdbTsDbUser = $mycnf['user'];
$wdbTsDbPassword = $mycnf['password'];
unset( $mycnf );

/**
 * Config for IRC bot
 */
// The 'replag' command only shows dbs with a replag higher than this number of seconds
$wdbDefaultMaxIgnoreLag = 2;
