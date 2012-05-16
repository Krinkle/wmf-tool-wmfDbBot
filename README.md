#wmfDbBot

wmfDbBot is a PHP interface to retrieve information about the databases of Wikimedia Foundation wikis. It displays two types of information. One is static information about wiki database setup, based on the local copy of Wikimedia's `db.php`. The other is replication lag information, which is retrieved by making an API request to a wiki hosted in the specified cluster.

## Installation

wmfDbBot requires PHP 5.3.2 or higher.

1. Get started by cloning the repository:
   <br>`git clone https://github.com/Krinkle/ts-krinkle-Kribo.git`
1. Initialize sub modules:
   <br>`git submodule update --init`
1. Make sure you set the following in `./LocalConfig.php`:
 * `wdbContact`
 * `wdbDefaultSection`
1. Create an `./externals/` and `./logs/` directory
1. Run `php ./maintenance/updateExternals.php`

## Commands

### Info
* Format: `info [identifier]`
  <br>Retrieves information about the specified identifier.


### Replag

* Format: `replag`
  <br>Show list of only the dbs having a replication lag higher than the configured threshold.
* Format: `replag [identifier]`
  <br>Show all dbs related to the specified identifier and their replication lag (including ones with a lag lower than the threshold).
* Format: `replag all`
  <br>Show all dbs of all cluster sections and their current replication lag.

### Externals

* Format `externals`
  <br>Shows timestamps of when the files in the ./externals directory were last modified.
* Format `externals update`
  <br>_(restricted to trusted users)_
  <br>Runs `./maintenance/updateExternals.php` in the background.

## Terms

* _identifier_
  <br>Can be a "section", "dbhost" or "dbname".
* _section_
  <br>Symbolic name of a database cluster section used to group one or more wikis (for example "s1", "s2", "s3" etc. or "default").
* _dbhost_
  <br>Name of a database host (such as "db5").
* _dbname_
  <br>Database name of a wiki (for example "aawiki", "enwiki", "nlwiktionary" or "frwikibooks").

## Maintenance scripts

### updateExternals.php

This script fetches the latest version of needed external resources that provide information.

## Kribo plugin

There is a bridging plugin available for Kribo to get this information available from an IRC bot:

* [wmfDbBot_KriboBridge](https://github.com/Krinkle/ts-krinkle-Kribo-plugins/wmfDbBot_KriboBridge)

## License
wmfDbBot is available under the [Creative Commons Attribution 3.0 Unported](https://creativecommons.org/licenses/by-sa/3.0/) license.
