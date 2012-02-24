<?php


$wdbContact  = 'krinklemail@gmail.com';

$wdbDefaultSection = 's3';

$wdbDefaultSectionWiki = 'mediawikiwiki';

$mycnf = parse_ini_file( '/home/krinkle/.my.cnf' );
$wdbTsDbUser = $mycnf['user'];
$wdbTsDbPassword = $mycnf['password'];
unset( $mycnf );
