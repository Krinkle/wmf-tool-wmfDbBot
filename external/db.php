<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# $Id$

$wgLBFactoryConf = array(

'class' => 'LBFactory_Multi',

'sectionsByDB' => array(
	'enwiki' => 's1',

	# New master
	'bgwiki' => 's2',
	'bgwiktionary' => 's2',
	'cswiki' => 's2',
	'enwikiquote' => 's2',
	'enwiktionary' => 's2',
	'eowiki' => 's2',
	'fiwiki' => 's2',
	'idwiki' => 's2',
	'itwiki' => 's2',
	'nlwiki' => 's2',
	'nowiki' => 's2',
	'plwiki' => 's2',
	'ptwiki' => 's2',
	'svwiki' => 's2',
	'thwiki' => 's2',
	'trwiki' => 's2',
	'zhwiki' => 's2',
	
	'dewiki' => 's5',
	'commonswiki' => 's4',
	
	
	'frwiki' => 's6',
	'jawiki' => 's6',
	'ruwiki' => 's6',

	'eswiki' => 's7',
	'huwiki' => 's7', 
	'hewiki' => 's7',
	'ukwiki' => 's7',
	'frwiktionary' => 's7',
	'metawiki' => 's7',
	'arwiki' => 's7',
	'centralauth' => 's7',
	'cawiki' => 's7',
	'viwiki' => 's7',
	'fawiki' => 's7',
	'rowiki' => 's7',
	'kowiki' => 's7',
),

# Load lists
#
# All servers which replicate the given databases should be in the load 
# list, not commented out, because otherwise maintenance scripts such 
# as compressOld.php won't wait for those servers when they lag. 
#
# Conversely, all servers which are down or do not replicate should be 
# removed, not set to load zero, because there are certain situations 
# when load zero servers will be used, such as if the others are lagged.
# Servers which are down should be removed to avoid a timeout overhead
# per invocation.
#
'sectionLoads' => array(
	's1' => array( 
		'db36'     => 0,
		'db32'     => 400,
		'db12'	    => 50,
		'db26'      => 100, # Snapshot hsot
		'db38'      => 400, # mysql hung, depooled, repooled with lower load. 20110326 -- mark
	),
	's2' => array(
		'db13'    => 0,
		'db30'    => 200,
		#'db15'    => 200, # went down 2011-01-12 - mark
		'db24'	  => 100, # Snapshot host
	),
	's4' => array(
		'db31'   => 0,
		'db22'   => 100,
		#'db5'    => 100,
		'db33'	  => 100, # Snapshot host
	),
	's5' => array(
		'db23'   => 0,
		'db14'   => 100,
		'db35'	   => 2000, # 5.1, don't copy over 4.0 hosts
		# down # 'db28'     => 2000, # 5.1..
	),
	/* s3 */ 'DEFAULT' => array( 
		'db39'    => 0,
		'db34'    => 100,
		'db25'	   => 1000,
		#'db11'	   => 2000, # /a inaccessible, -mark 20110418
	),
	's6' => array(
		'db29'     => 0,
		'db21'     => 100, # snapshot host
		'db43'     => 1500
                #'db19'     => 3000,
                #'db7'     => 1500, # replacing with db43
	),
	's7' => array(
		'db37' => 0,
		'db18' => 50,  # 20110730 - is racking up ECC errors
		'db16'	=> 1000,
	),
),


'serverTemplate' => array(
    'dbname'      => $wgDBname,
    'user'        => $wgDBuser,
    'password'    => $wgDBpassword,
    'type'        => 'mysql',
    'flags'       => DBO_DEFAULT,
    'max lag'     => 30,
    #'max threads' => 350, -- disabled TS
),

'groupLoadsBySection' => array(
	/*
	's2' => array(
		'QueryPage::recache' => array(
			'db8' => 100,
		)
	)*/
),


'groupLoadsByDB' => array(
	'enwiki' => array(
		'watchlist' => array(
			'db12' => 1,
		),
		'recentchangeslinked' => array(
			'db12' => 1,
		),
		'contributions' => array(
			'db12' => 1,
		),
		'dump' => array(
			'db12' => 1,
		),
	),
),

# Hosts settings
# Do not remove servers from this list ever
# Removing a server from this list does not remove the server from rotation,
# it just breaks the site horribly.
'hostsByName' => array(
	'thistle'  => '10.0.0.232', # do not remove or comment out
	'db4'      => '10.0.0.237', # do not remove or comment out
	'db5'      => '10.0.0.238', # do not remove or comment out
	'db7'      => '10.0.0.240', # do not remove or comment out
	'db8'      => '10.0.0.241', # do not remove or comment out
	'db11'     => '10.0.6.21', # do not remove or comment out
	'db12'     => '10.0.6.22', # do not remove or comment out
	'db13'     => '10.0.6.23', # do not remove or comment out
	'db14'     => '10.0.6.24', # do not remove or comment out
	'db15'     => '10.0.6.25', # do not remove or comment out
	'db16'     => '10.0.6.26', # do not remove or comment out
	'db17'     => '10.0.6.27', # do not remove or comment out
	'db18'     => '10.0.6.28', # do not remove or comment out
	'db19'     => '10.0.6.29', # do not remove or comment out
	'db20'     => '10.0.6.30', # do not remove or comment out
	'db21'     => '10.0.6.31', # do not remove or comment out
	'db22'     => '10.0.6.32', # do not remove or comment out
	'db23'     => '10.0.6.33', # do not remove or comment out
	'db24'     => '10.0.6.34', # do not remove or comment out
	'db25'     => '10.0.6.35', # do not remove or comment out
	'db26'     => '10.0.6.36', # do not remove or comment out
	'db27'     => '10.0.6.37', # do not remove or comment out
	'db28'     => '10.0.6.38', # do not remove or comment out
	'db29'     => '10.0.6.39', # do not remove or comment out
	'db30'     => '10.0.6.40', # do not remove or comment out
	'db31'     => '10.0.6.41', # do not remove or comment out
	'db32'     => '10.0.6.42', # do not remove or comment out
	'db33'     => '10.0.6.43', # do not remove or comment out
	'db34'     => '10.0.6.44', # do not remove or comment out
	'db35'     => '10.0.6.45', # do not remove or comment out
	'db36'     => '10.0.6.46', # do not remove or comment out
	'db37'     => '10.0.6.47', # do not remove or comment out
	'db38'     => '10.0.6.48', # do not remove or comment out
	'db39'     => '10.0.6.49', # do not remove or comment out
	'db40'     => '10.0.6.50', # do not remove or comment out
	'db43'     => '10.0.6.53', # do not remove or comment out

),

'externalLoads' => array(
	# Recompressed stores
	'rc1' => array(
		'10.0.0.248' => 1, # ms3
		'10.0.0.249' => 1, # ms2
		'10.0.0.250' => 1, # ms1
	),

	# Ubuntu dual-purpose stores
	'cluster3' => array(
		'10.0.2.151' => 1,
		'10.0.2.163' => 1,
		'10.0.2.175' => 1,
	),
	'cluster4' => array(
		'10.0.2.152' => 1,
		'10.0.2.164' => 1,
		'10.0.2.176' => 1,
	),
	'cluster5' => array(
		'10.0.2.153' => 1,
		'10.0.2.165' => 1,
		'10.0.2.177' => 1,
	),
	'cluster6' => array(
		#'10.0.2.154' => 1, # shut down, RT 1134
		'10.0.2.166' => 1,
		'10.0.2.178' => 1,
	),
	'cluster7' => array(
		'10.0.2.155' => 1,
		'10.0.2.167' => 1,
		#'10.0.2.179' => 1,
	),
	'cluster8' => array(
		'10.0.2.156' => 1,
		'10.0.2.168' => 1,
		#'10.0.2.180' => 1,
	),
	'cluster9' => array(
		'10.0.2.157' => 1,
		#'10.0.2.169' => 1,
		'10.0.2.181' => 1,
	),
	'cluster10' => array(
		'10.0.2.158' => 1,
		'10.0.2.170' => 1,
		'10.0.2.182' => 1,
	),

	'cluster20' => array(
		'10.0.2.160' => 50,
		'10.0.2.172' => 100,
		'10.0.2.184' => 100,
	),
	'cluster21' => array(
		'10.0.2.161' => 50,
		#'10.0.2.173' => 100,
		'10.0.2.185' => 100,
	),

	# Dedicated server stores
	'cluster22' => array(
		'10.0.0.248' => 1, # ms3
		'10.0.0.249' => 1, # ms2
		'10.0.0.250' => 1, # ms1
	),

	# Clusters required for bug 22624
	'cluster1' => array(
		'10.0.7.1' => 1,
		#'10.0.7.101' => 1,
	),
	'cluster2' => array(
		'10.0.7.1' => 1,
		#'10.0.7.101' => 1,	
	),

	# Obsolete ex-fedora clusters
	/*
	'cluster11' => array(
		'10.0.7.11' => 1,
		#'10.0.7.111' => 1,
	),
	'cluster12' => array(
		'10.0.7.12' => 1,
		#'10.0.7.112' => 1,
	),
	'cluster16' => array(
		'10.0.7.16' => 1,
		#'10.0.7.116' => 1,
	),
	'cluster17' => array(
		'10.0.7.17' => 1,
		#'10.0.7.117' => 1,
	),
	'cluster18' => array(
		'10.0.7.18' => 1,
		#'10.0.7.118' => 1,
	),
	'cluster19' => array(
		'10.0.7.19' => 1,
		#'10.0.7.119' => 1,
	),
	 */
),

'masterTemplateOverrides' => array(
	# The master generally has more threads running than the others
	'max threads' => 400,
),

'externalTemplateOverrides' => array(
	'flags' => 0, // No transactions
),

'templateOverridesByCluster' => array(
	'cluster1'  => array( 'blobs table' => 'blobs_cluster1' ),
	'cluster2'  => array( 'blobs table' => 'blobs_cluster2' ),
	'cluster22' => array( 'blobs table' => 'blobs_cluster22' ),
),

# This key must exist for the master switch script to work
'readOnlyBySection' => array(
#'DEFAULT' => 'Emergency maintenance in progress',
#'s7'      => 'Emergency maintenance in progress',
),

);

$wgDefaultExternalStore = array( 
	'DB://cluster22',
);
$wgMasterWaitTimeout = 2;
$wgDBAvgStatusPoll = 30000;

#$wgLBFactoryConf['readOnlyBySection']['s2'] =
#$wgLBFactoryConf['readOnlyBySection']['s2a'] =
#'Emergency maintenance, need more servers up, new estimate ~18:30 UTC';

if ( $wgDBname === 'testwiki' ) {
	$wgLBFactoryConf['serverTemplate']['max threads'] = 300;
}