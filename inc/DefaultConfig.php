<?php

/**
 * General
 */
$wdbVersion = '0.4.1';

// User agent etc.
$wdbContact = null;

/**
 * Config for externals
 */
// Server config: Default section name for wikis without a section or in section 'DEFAULT'
$wdbDefaultSection = 's3';

// Server config: Any wiki that is in the default section, used to get replag for 'DEFAULT'
$wdbDefaultSectionWiki = null;

// Database access
$wdbTsDbUser = null;
$wdbTsDbPassword = null;

/**
 * Config for IRC bot
 */
$wdbNickname = 'wmfDbBot';

// The 'replag' command only shows dbs with a replag higher than this number of seconds
$wdbDefaultMaxIgnoreLag = 1;
