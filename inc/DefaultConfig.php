<?php
/**
 * @author Timo Tijhof, 2011–2013
 * @since 0.1
 * @package wmfDbBot
 */

/**
 * General
 */
$wdbVersion = '0.4.2';

// E-mailadress, right now only used in the user agent for API requests.
$wdbContact = null;

/**
 * Config for externals
 */
// Server config: Default section name for wikis without a section or in section 'DEFAULT' (e.g. 's3')
$wdbDefaultSection = null;

// Path to ts-krinkle-getWikiAPI install
$wdbTsGetWikiAPI = 'http://toolserver.org/~krinkle/getWikiAPI/';

/**
 * Config for interface (defaults to st-out)
 */
$wdbNickname = 'wmfDbBot';

// The 'replag' command only lists sections with a replag higher than this threshold
// (in number of seconds). 'replag all' is not affected by the threshold.
$wdbReplagThreshold = 0;
