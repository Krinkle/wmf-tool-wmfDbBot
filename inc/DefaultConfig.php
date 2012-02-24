<?php

/**
 * General
 */
$wdbVersion = '0.4.1';

// E-mailadress, right now only used in the user agent for API requests.
$wdbContact = null;

/**
 * Config for externals
 */
// Server config: Default section name for wikis without a section or in section 'DEFAULT' (e.g. 's3')
$wdbDefaultSection = null;

// Server config: Any wiki that is in the default section, used to get replag for 'DEFAULT' (e.g. 'mediawikiwiki')
$wdbDefaultSectionWiki = null;

// Database access to sql.toolserver.org
$wdbTsDbUser = null;
$wdbTsDbPassword = null;

/**
 * Config for interface (defaults to st-out)
 */
$wdbNickname = 'wmfDbBot';

// The 'replag' command only shows dbs with a replag higher than this number of seconds
$wdbDefaultMaxIgnoreLag = 1;
